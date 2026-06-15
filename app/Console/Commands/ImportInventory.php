<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CatalogTermTranslator;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use SimpleXMLElement;
use ZipArchive;

class ImportInventory extends Command
{
    protected $signature = 'inventory:import {file} {--dry-run} {--limit=0} {--refresh-translations}';

    protected $description = 'Import inventory products from XLSX files';

    private array $categoryCache = [];

    private array $translationCache = [];

    private array $translationColumnCache = [];

    public function handle(): int
    {
        $filePath = (string) $this->argument('file');

        if (! is_file($filePath)) {
            $this->error('File not found.');

            return self::FAILURE;
        }

        $this->categoryCache = Category::query()->pluck('id', 'name')->all();

        $createdProducts = 0;
        $updatedProducts = 0;
        $createdVariants = 0;
        $updatedVariants = 0;
        $skippedRows = 0;
        $processedRows = 0;
        $limit = max(0, (int) $this->option('limit'));
        $totalRows = $this->countInventoryRowsForProgress($filePath, $limit);
        $progressBar = null;
        $lastVariantParentBySheet = [];

        if (! $this->option('dry-run') && $totalRows > 0) {
            $progressBar = $this->output->createProgressBar($totalRows);
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%');
            $progressBar->start();
        }

        foreach ($this->readInventoryRows($filePath) as $row) {
            if ($limit > 0 && $processedRows >= $limit) {
                break;
            }

            $productData = $this->parseProductData($row);

            if ($productData === null) {
                $skippedRows++;
                $progressBar?->advance();

                continue;
            }

            $sheetKey = $row['sheet'];

            if ($this->isStandaloneVariantRow($productData) && isset($lastVariantParentBySheet[$sheetKey])) {
                $productData = [
                    ...$lastVariantParentBySheet[$sheetKey],
                    'variant_name' => $productData['name'],
                ];
            }

            if ($this->canBeVariantParent($productData)) {
                $lastVariantParentBySheet[$sheetKey] = [
                    'name' => $productData['name'],
                    'variant_name' => null,
                    'category_name' => $productData['category_name'],
                    'slug' => $productData['slug'],
                ];
            }

            $processedRows++;

            if ($this->option('dry-run')) {
                $variant = $productData['variant_name'] ? " / {$productData['variant_name']}" : '';
                $this->line("{$productData['category_name']}: {$productData['name']}{$variant} | qty {$row['quantity']} | EUR {$row['price']}");

                continue;
            }

            DB::transaction(function () use (
                $productData,
                $row,
                &$createdProducts,
                &$updatedProducts,
                &$createdVariants,
                &$updatedVariants
            ): void {
                $product = Product::query()->firstOrNew(['slug' => $productData['slug']]);

                $product->name = $productData['name'];
                $this->fillCatalogTranslations($product, [
                    'name' => $productData['name'],
                ], true);

                if ($productData['variant_name'] === null) {
                    $product->price = $row['price'];
                    $product->quantity = $row['quantity'];
                    $product->stock = $row['quantity'] > 0;
                }

                if (filled($product->description)) {
                    $this->fillCatalogTranslations($product, [
                        'description' => (string) $product->description,
                    ], false);
                }

                if (filled($product->extra_information)) {
                    $this->fillCatalogTranslations($product, [
                        'extra_information' => (string) $product->extra_information,
                    ], false);
                }

                $product->exists ? $updatedProducts++ : $createdProducts++;
                $product->saveQuietly();

                $product->categories()->syncWithoutDetaching([
                    $this->getCategoryId($productData['category_name']),
                ]);

                if ($productData['variant_name'] !== null) {
                    $variant = ProductVariant::query()->firstOrNew([
                        'product_id' => $product->id,
                        'size' => $productData['variant_name'],
                    ]);

                    $this->fillCatalogTranslations($variant, [
                        'size' => $productData['variant_name'],
                    ], true);
                    $variant->price = $row['price'];
                    $variant->quantity = $row['quantity'];
                    $variant->stock = $row['quantity'] > 0;

                    $variant->exists ? $updatedVariants++ : $createdVariants++;
                    $variant->save();

                    $this->syncProductFromVariants($product);
                }
            });

            $progressBar?->advance();
        }

        if ($progressBar !== null) {
            $progressBar->finish();
        }

        $this->newLine();
        $this->info('Import finished.');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Processed rows', $processedRows],
                ['Skipped rows', $skippedRows],
                ['Created products', $createdProducts],
                ['Updated products', $updatedProducts],
                ['Created variants', $createdVariants],
                ['Updated variants', $updatedVariants],
            ]
        );

        return self::SUCCESS;
    }

    private function readInventoryRows(string $path): \Generator
    {
        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            return;
        }

        $sharedStrings = $this->readSharedStrings($zip);
        $sheets = $this->readSheets($zip);

        foreach ($sheets as $sheet) {
            $index = $zip->locateName($sheet['path']);

            if ($index === false) {
                continue;
            }

            $xml = simplexml_load_string($zip->getFromIndex($index));

            if (! $xml instanceof SimpleXMLElement || ! isset($xml->sheetData->row)) {
                continue;
            }

            foreach ($xml->sheetData->row as $row) {
                $cells = [];

                foreach ($row->c as $cell) {
                    $attributes = $cell->attributes();
                    $reference = (string) ($attributes['r'] ?? '');
                    $column = preg_replace('/[0-9]/', '', $reference);

                    if ($column === '') {
                        continue;
                    }

                    $cells[$column] = $this->cellValue($cell, $sharedStrings);
                }

                $name = $this->cleanText($cells['B'] ?? '');

                if ($name === '' || $this->isHeaderRow($name)) {
                    continue;
                }

                yield [
                    'sheet' => $this->cleanText($sheet['name']),
                    'name' => $name,
                    'unit' => $this->cleanText($cells['C'] ?? ''),
                    'quantity' => $this->toInt($cells['D'] ?? null),
                    'price' => $this->toFloat($cells['F'] ?? null),
                    'price_bgn' => $this->toFloat($cells['G'] ?? null),
                ];
            }
        }

        $zip->close();
    }

    private function countInventoryRowsForProgress(string $path, int $limit): int
    {
        $rows = 0;
        $processed = 0;

        foreach ($this->readInventoryRows($path) as $row) {
            if ($limit > 0 && $processed >= $limit) {
                break;
            }

            $rows++;

            if ($this->parseProductData($row) !== null) {
                $processed++;
            }
        }

        return $rows;
    }

    private function readSharedStrings(ZipArchive $zip): array
    {
        $index = $zip->locateName('xl/sharedStrings.xml');

        if ($index === false) {
            return [];
        }

        $xml = simplexml_load_string($zip->getFromIndex($index));

        if (! $xml instanceof SimpleXMLElement) {
            return [];
        }

        $strings = [];

        foreach ($xml->si as $value) {
            if (isset($value->t)) {
                $strings[] = (string) $value->t;

                continue;
            }

            $parts = [];

            foreach ($value->r as $run) {
                $parts[] = (string) ($run->t ?? '');
            }

            $strings[] = implode('', $parts);
        }

        return $strings;
    }

    private function readSheets(ZipArchive $zip): array
    {
        $workbookIndex = $zip->locateName('xl/workbook.xml');
        $relsIndex = $zip->locateName('xl/_rels/workbook.xml.rels');

        if ($workbookIndex === false || $relsIndex === false) {
            return [];
        }

        $workbook = simplexml_load_string($zip->getFromIndex($workbookIndex));
        $rels = simplexml_load_string($zip->getFromIndex($relsIndex));

        if (! $workbook instanceof SimpleXMLElement || ! $rels instanceof SimpleXMLElement) {
            return [];
        }

        $relationshipMap = [];

        foreach ($rels->Relationship as $relationship) {
            $attributes = $relationship->attributes();
            $relationshipMap[(string) $attributes['Id']] = (string) $attributes['Target'];
        }

        $sheets = [];

        foreach ($workbook->sheets->sheet as $sheet) {
            $attributes = $sheet->attributes();
            $relationshipAttributes = $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
            $relationshipId = (string) $relationshipAttributes['id'];
            $target = $relationshipMap[$relationshipId] ?? null;

            if ($target === null) {
                continue;
            }

            $sheets[] = [
                'name' => (string) $attributes['name'],
                'path' => Str::startsWith($target, 'xl/') ? $target : 'xl/'.ltrim($target, '/'),
            ];
        }

        return $sheets;
    }

    private function cellValue(SimpleXMLElement $cell, array $sharedStrings): string
    {
        $attributes = $cell->attributes();
        $type = (string) ($attributes['t'] ?? '');

        if ($type === 'inlineStr') {
            return $this->cleanText((string) ($cell->is->t ?? ''));
        }

        $value = (string) ($cell->v ?? '');

        if ($type === 's') {
            return $this->cleanText($sharedStrings[(int) $value] ?? '');
        }

        return $this->cleanText($value);
    }

    private function parseProductData(array $row): ?array
    {
        $name = $this->cleanProductName($row['name']);

        if ($name === '' || ! $this->isProductRow($row)) {
            return null;
        }

        $categoryName = $this->normalizeCategoryName($row['sheet']);
        [$productName, $variantName] = $this->splitProductAndVariant($name);

        return [
            'name' => $productName,
            'variant_name' => $variantName,
            'category_name' => $categoryName,
            'slug' => $this->slugFor($productName, $categoryName),
        ];
    }

    private function splitProductAndVariant(string $name): array
    {
        if (preg_match('/^N\s+.+$/u', $name)) {
            return [$name, null];
        }

        if (preg_match('/^(?<product>R)\s+(?<variant>.+)$/u', $name, $matches)) {
            return [
                $this->cleanProductName($matches['product']),
                $this->cleanProductName($matches['variant']),
            ];
        }

        if (preg_match('/^(?<product>ДОРНИК\s+ЦАНГОВ)\s+(?<variant>.+)$/iu', $name, $matches)) {
            return [
                $this->cleanProductName($matches['product']),
                $this->cleanProductName($matches['variant']),
            ];
        }

        if (preg_match('/^(?<product>ДОРНИК)\s+(?<variant>ЗА\s+ФРЕЗА)\s+(?<iso>ИСО\s*\d+)$/iu', $name, $matches)) {
            return [
                $this->cleanProductName($matches['product'].' '.$matches['iso']),
                $this->cleanProductName($matches['variant']),
            ];
        }

        if (preg_match('/^(?<product>.+?)\s*-?\s*ф\s*(?<variant>\d+(?:[.,]\d+)?(?:.*)?)$/iu', $name, $matches)) {
            return [
                $this->cleanProductName($matches['product']),
                $this->cleanProductName('Ф'.str_replace(',', '.', $matches['variant'])),
            ];
        }

        foreach ($this->variantTokenPatterns() as $pattern) {
            if (! preg_match($pattern, $name, $matches)) {
                continue;
            }

            return [
                $this->cleanProductName($matches['product']),
                $this->cleanProductName($matches['variant']),
            ];
        }

        return [$name, null];
    }

    private function canBeVariantParent(array $productData): bool
    {
        return $productData['name'] === 'R';
    }

    private function isStandaloneVariantRow(array $productData): bool
    {
        return $productData['variant_name'] === null
            && (bool) preg_match('/^\d+(?:[.,]\d+)?(?:\s+\S+)?$/u', $productData['name']);
    }

    private function variantTokenPatterns(): array
    {
        return [
            '/^(?<product>ПИЛА\s+\S+)\s+(?<variant>L\s*\d{1,4}.*)$/iu',
            '/^(?<product>ДРЪЖКА\s+ЗА\s+ПИЛА)\s+(?<variant>.+)$/iu',
            '/^(?<product>ДЪРЖАЧ\s+ЗА\s+N\s*\d+(?:[.,]\d+)?)\s*-\s*(?<variant>\d+.*)$/iu',
            '/^(?<product>АВАНС\.\s+ПЛАЩАНЕ\s+50%\s+НОЖ\s+ЗА\s+\S+)\s+(?<variant>\d+(?:[.,]\d+)?\s*[xх]\s*\d+(?:[.,]\d+)?(?:\s*[xх]\s*\d+(?:[.,]\d+)?)?.*)$/iu',
            '/^(?<product>НОЖ\s+ЗА\s+ВЪНШНО\s+ПРЕСТЪРГВАНЕ)\s+(?<variant>\d+(?:[.,]\d+)?\s*[xх]\s*\d+(?:[.,]\d+)?.*)$/iu',
            '/^(?<product>НОЖ\s+ЗА\s+ВЪТРЕШНО\s+ПРЕСТЪРГВАНЕ)\s+(?<variant>\d+(?:[.,]\d+)?\s*[xх]\s*\d+(?:[.,]\d+)?.*)$/iu',
            '/^(?<product>НОЖ\s+ЗА\s+ЧЕЛНО\s+ПРЕСТ\.\s+45\s+ГРАДУСА)\s+(?<variant>\d+(?:[.,]\d+)?\s*[xх]\s*\d+(?:[.,]\d+)?.*)$/iu',
            '/^(?<product>НОЖ\s+ОТРЕЗЕН)\s+(?<variant>\d+(?:[.,]\d+)?\s*[xх]\s*\d+(?:[.,]\d+)?.*)$/iu',
            '/^(?<product>НОЖ\s+ПРОХОДНО\s+ИЗВИТ\s+45ГР)\s+(?<variant>.+)$/iu',
            '/^(?<product>НОЖ\s+ЗА\s+ПРОХОДЕН\s+ОТВОР)\s+(?<variant>ISO\s*\d+.*)$/iu',
            '/^(?<product>НОЖ\s+ЗА\s+ВЪНШНА\s+РЕЗБА)\s+(?<variant>ISO\s*\d+.*)$/iu',
            '/^(?<product>НОЖ\s+ЗА\s+ВЪТРЕШНА\s+РЕЗБА)\s+(?<variant>(?:ISO\s*\d+\s*)?[KКPРMМ]\s*\d+.*)$/iu',
            '/^(?<product>НОЖ\s+ЗА\s+ГЛУХ\s+ОТВОР)\s+(?<variant>(?:ISO|ИСО)\s*\d+.*)$/iu',
            '/^(?<product>НОЖ\s+ИЗВИТ\s+30ГР)\s+(?<variant>[KКPРMМ]\s*\d+.*)$/iu',
            '/^(?<product>НОЖ\s+ПРАВ)\s+(?<variant>ISO\s*\d+.*)$/iu',
            '/^(?<product>НОЖ\s+ПРОРЕЗЕН)\s+(?<variant>(?:ISO|ИСО|[PMРМ])\s*\d+.*)$/iu',
            '/^(?<product>НОЖ\s+ЧИСТ)\s+(?<variant>(?:ISO|ИСО)\s*\d+.*)$/iu',
            '/^(?<product>НОЖ\s+УПОРЕН)\s+(?<variant>(?:(?:ISO|ИСО)\s*\d+\s*)?[KКPРMМ]\s*\d+.*)$/iu',
            '/^(?<product>НОЖ\s+ЗА\s+ТРАП\\.?\s+КАНАЛ)\s+(?<variant>(?:ISO|ИСО)\s*\d+.*)$/iu',
            '/^(?<product>ФРЕЗА\s+ТРИСТР)\s+(?<variant>.+)$/iu',
            '/^(?<product>ФРЕЗА\s+ЧЕЛ\s+ЦИЛ)\s+(?<variant>.+)$/iu',
            '/^(?<product>ФРЕЗА\s+ДОРН\s+3ТП)\s+(?<variant>[A-ZА-Я]\s*\d+.*)$/iu',
            '/^(?<product>ФРЕЗА\s+ЧЕРВЯЧНА\s+ОПАШКОВА\s+[AА]\s*\d+)\s+(?<variant>[MМ]\s*\d+(?:[.,]\d+)?.*)$/iu',
            '/^(?<product>ДЪЛБЯК\s+ДИСКОВ)\s+(?<variant>[MМ]\s*\d+(?:[.,]\d+)?.*)$/iu',
            '/^(?<product>ДЪЛБЯК\s+ЧАШКОВИД)\s+(?<variant>[MМ]\s*\d+(?:[.,]\d+)?\s+A\s*\d+.*)$/iu',
            '/^(?<product>ЛИСТ\s+МЕХАНИЧНА\s+НОЖОВКА)\s+(?<variant>L\s*\d+\s*\/\s*\d+\s*\/\s*\d+(?:[.,]\d+)?.*)$/iu',
            '/^(?<product>МЕТЧИК\s+РЪЧЕН\s+ТР)\s+(?<variant>.+)$/iu',
            '/^(?<product>МЕТЧИК\s+РЪЧЕН)\s+(?<variant>PX\s*\d+.*)$/iu',
            '/^(?<product>ПЛАШКА\s+ТР)\s+(?<variant>\d+.*)$/iu',
            '/^(?<product>ТЕКСТОЛИТ\s+НА\s+ЛИСТ)\s+(?<variant>\d+(?:[.,]\d+)?\s*(?:mm|мм).*)$/iu',
            '/^(?<product>ОПАШКА\s+ЗА\s+ПАТР)\s+(?<variant>[A-ZА-Я]\s*\d+.*)$/iu',
            '/^(?<product>СЪЕДИН\s+КРЪСТАТ\s+ЗА\s+ДОРН)\s+(?<variant>[A-ZА-Я]+\s*\d+.*)$/iu',
            '/^(?<product>ДОРНИК\s+ИСО\s*\d+)\s+(?<variant>.+)$/iu',
            '/^(?<product>ДОРНИК\s+ЗА\s+ИНСТР\s+ФРЕЗА)\s+(?<variant>МК\s*\d+.*)$/iu',
            '/^(?<product>ДОРНИК\s+ЗА\s+ФРЕЗОВА\s+ГЛАВА)\s+(?<variant>.+)$/iu',
            '/^(?<product>ДОРНИК\s+С\s+ФЛАНЕЦ)\s+(?<variant>.+)$/iu',
            '/^(?<product>ДОРНИК\s+ЦАНГОВ)\s+(?<variant>.+)$/iu',
            '/^(?<product>ДОРНИК)\s+(?<variant>ЗА\s+ПАТРОННИК.*)$/iu',
            '/^(?<product>.+?\/[A-Z0-9]+\/)\s*(?<variant>\d+(?:[.,]\d+)?\s*(?:mm|мм)\b.*)$/iu',
            '/^(?<product>.+?\/[A-Z0-9]+\/)\s*(?<variant>[A-Z]{1,3}\s*\d{1,4}.*)$/iu',
            '/^(?<product>[A-Z]{3,6})\s+(?<variant>[A-Z0-9][A-Z0-9.]*.*)$/u',
            '/^(?<product>.+?)\s+(?<variant>МК\s*\d+.*)$/u',
            '/^(?<product>.+?)\s+(?<variant>\d+(?:[.,]\d+)?\s+[A-ZА-Я]\b.*)$/u',
            '/^(?<product>.+?)\s+(?<variant>\d+(?:[.,]\d+)?\s*\/\s*\d+(?:[.,]\d+)?(?:\s*\/\s*\d+(?:[.,]\d+)?)?.*)$/u',
            '/^(?<product>.+?)\s+(?<variant>\d+(?:[.,]\d+)?\s*[xх]\s*\d+(?:[.,]\d+)?(?:\s*[xх]\s*\d+(?:[.,]\d+)?)?.*)$/u',
            '/^(?<product>.+?)\s+(?<variant>\d+(?:[.,]\d+)?\s*-\s*\d+(?:[.,]\d+)?.*)$/u',
            '/^(?<product>.+?)\s+(?<variant>[PР]\s*\d{1,4}.*)$/u',
            '/^(?<product>.+?)\s+(?<variant>[A-ZА-Я]\s*\d+(?:[.,]\d+)?(?:.*)?)$/u',
            '/^(?<product>.+?)\s+(?<variant>\d+(?:[.,]\d+)?\s*(?:УДАРЕН|ЕДНОСТР|ЗВЕЗДА)?\/?.*)$/u',
            '/^(?<product>.+?)\s+(?<variant>\d+(?:[.,]\d+)?\s+КАРАТА?.*)$/u',
            '/^(?<product>.+?)\s+(?<variant>\d+(?:[.,]\d+)?\s*К-Т.*)$/u',
        ];
    }

    private function syncProductFromVariants(Product $product): void
    {
        $stats = $product->variants()
            ->selectRaw('MIN(price) as min_price')
            ->first();

        $product->forceFill([
            'price' => $stats->min_price,
            'quantity' => 0,
            'stock' => $product->variants()->where('quantity', '>', 0)->exists(),
        ])->saveQuietly();
    }

    private function getCategoryId(string $name): int
    {
        if (! isset($this->categoryCache[$name])) {
            $category = Category::query()->firstOrCreate(['name' => $name]);
            $this->fillCatalogTranslations($category, [
                'name' => $name,
            ], true);

            if ($category->isDirty()) {
                $category->saveQuietly();
            }

            $this->categoryCache[$name] = $category->id;
        }

        return $this->categoryCache[$name];
    }

    private function fillCatalogTranslations(Model $model, array $sourceFields, bool $uppercase = true): void
    {
        if (! (bool) config('catalog_translation.translate_during_import', true)) {
            return;
        }

        $refreshTranslations = (bool) $this->option('refresh-translations');

        foreach ($this->translationLocales() as $locale) {
            foreach ($sourceFields as $field => $value) {
                $translatedField = "{$field}_{$locale}";

                if (
                    ! $this->hasTranslationColumn($model, $translatedField)
                    || (! $refreshTranslations && filled($model->{$translatedField}))
                    || blank($value)
                ) {
                    continue;
                }

                $translated = $this->shouldCopyWithoutTranslation((string) $value)
                    ? $this->cleanProductName((string) $value)
                    : $this->translateCatalogText((string) $value, $locale, $uppercase);

                if (filled($translated)) {
                    $model->{$translatedField} = $translated;
                }
            }
        }
    }

    private function translationLocales(): array
    {
        $locales = config('catalog_translation.locales', ['en']);

        if (is_string($locales)) {
            $locales = explode(',', $locales);
        }

        return array_values(array_unique(array_filter(
            array_map(static fn ($locale): string => strtolower(trim((string) $locale)), (array) $locales),
            static fn ($locale): bool => $locale !== '' && $locale !== 'bg'
        )));
    }

    private function hasTranslationColumn(Model $model, string $column): bool
    {
        $table = $model->getTable();
        $cacheKey = "{$table}.{$column}";

        return $this->translationColumnCache[$cacheKey] ??= Schema::hasColumn($table, $column);
    }

    private function shouldCopyWithoutTranslation(string $value): bool
    {
        $text = trim($value);

        if ($text === '') {
            return false;
        }

        return (bool) preg_match('/^(?:Ф\s*)?\d+(?:[.,]\d+)?(?:\s*\/\s*\d+(?:[.,]\d+)?)+$/iu', $text)
            || (bool) preg_match('/^(?:Ф\s*)?\d+(?:[.,]\d+)?(?:\s*[xх]\s*\d+(?:[.,]\d+)?)+(?:\s*[xх]\s*\d+(?:[.,]\d+)?)?$/iu', $text)
            || (bool) preg_match('/^(?:Ф\s*)?\d+(?:[.,]\d+)?\s*(?:mm|мм|cm|см|m|м)$/iu', $text);
    }

    private function translateCatalogText(string $value, string $locale, bool $uppercase = true): ?string
    {
        $text = trim($value);

        if ($text === '') {
            return null;
        }

        $cacheKey = "{$locale}:".($uppercase ? 'upper:' : 'plain:').$text;

        if (array_key_exists($cacheKey, $this->translationCache)) {
            return $this->translationCache[$cacheKey];
        }

        $translated = app(CatalogTermTranslator::class)->translateProviderOnly($text, $locale, 'bg');
        $translated = is_string($translated) ? trim($translated) : '';
        $translated = $uppercase ? Str::upper($translated) : $translated;

        return $this->translationCache[$cacheKey] = ($translated !== '' ? $translated : null);
    }

    private function normalizeCategoryName(string $name): string
    {
        return Str::upper(trim($name)) ?: 'ОБЩИ';
    }

    private function cleanProductName(string $value): string
    {
        $value = str_replace(["\xc2\xa0", '№'], [' ', 'N'], $value);
        $value = preg_replace('/\s+/u', ' ', $value);

        return Str::upper(trim((string) $value));
    }

    private function cleanText(mixed $value): string
    {
        return trim((string) $value);
    }

    private function isHeaderRow(string $name): bool
    {
        $upper = Str::upper($name);

        return str_contains($upper, 'К-ВО')
            || str_contains($upper, 'ЕВРО')
            || str_contains($upper, 'НАИМЕНОВАНИЕ');
    }

    private function isProductRow(array $row): bool
    {
        $unit = Str::lower(str_replace('.', '', $row['unit'] ?? ''));

        return $unit === 'бр';
    }

    private function toInt(mixed $value): int
    {
        $value = trim(str_replace(',', '.', (string) $value));

        if ($value === '' || ! is_numeric($value)) {
            return 0;
        }

        return max(0, (int) floor((float) $value));
    }

    private function toFloat(mixed $value): ?float
    {
        $value = trim(str_replace(',', '.', (string) $value));

        if ($value === '' || ! is_numeric($value)) {
            return null;
        }

        return round((float) $value, 2);
    }

    private function slugFor(string $productName, string $categoryName): string
    {
        $base = Str::slug($categoryName.' '.$productName);

        if ($base !== '') {
            return $base;
        }

        return 'product-'.md5($categoryName.'|'.$productName);
    }
}

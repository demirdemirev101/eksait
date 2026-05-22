<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SimpleXMLElement;
use ZipArchive;

class ImportInventory extends Command
{
    protected $signature = 'inventory:import {file} {--dry-run} {--limit=0}';

    protected $description = 'Import inventory products from XLSX files';

    private array $categoryCache = [];

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

        foreach ($this->readInventoryRows($filePath) as $row) {
            if ($limit > 0 && $processedRows >= $limit) {
                break;
            }

            $productData = $this->parseProductData($row);

            if ($productData === null) {
                $skippedRows++;
                continue;
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

                if ($productData['variant_name'] === null) {
                    $product->price = $row['price'];
                    $product->quantity = $row['quantity'];
                    $product->stock = $row['quantity'] > 0;
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

                    $variant->price = $row['price'];
                    $variant->quantity = $row['quantity'];
                    $variant->stock = $row['quantity'] > 0;

                    $variant->exists ? $updatedVariants++ : $createdVariants++;
                    $variant->save();

                    $this->syncProductFromVariants($product);
                }
            });
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
        $zip = new ZipArchive();

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
                'path' => Str::startsWith($target, 'xl/') ? $target : 'xl/' . ltrim($target, '/'),
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
        if (preg_match('/^(?<product>.+?)\s*-?\s*ф\s*(?<variant>\d+(?:[.,]\d+)?(?:.*)?)$/iu', $name, $matches)) {
            return [
                $this->cleanProductName($matches['product']),
                $this->cleanProductName('Ф' . str_replace(',', '.', $matches['variant'])),
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

    private function variantTokenPatterns(): array
    {
        return [
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
            ->selectRaw('MIN(price) as min_price, SUM(quantity) as total_quantity')
            ->first();

        $quantity = (int) ($stats->total_quantity ?? 0);

        $product->forceFill([
            'price' => $stats->min_price,
            'quantity' => $quantity,
            'stock' => $quantity > 0,
        ])->saveQuietly();
    }

    private function getCategoryId(string $name): int
    {
        if (! isset($this->categoryCache[$name])) {
            $category = Category::query()->firstOrCreate(['name' => $name]);
            $this->categoryCache[$name] = $category->id;
        }

        return $this->categoryCache[$name];
    }

    private function normalizeCategoryName(string $name): string
    {
        return Str::upper(trim($name)) ?: 'ОБЩИ';
    }

    private function cleanProductName(string $value): string
    {
        $value = str_replace(["\xc2\xa0", '№'], [' ', 'N'], $value);
        $value = preg_replace('/\s+/u', ' ', $value);

        return trim((string) $value);
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
        $base = Str::slug($categoryName . ' ' . $productName);

        if ($base !== '') {
            return $base;
        }

        return 'product-' . md5($categoryName . '|' . $productName);
    }
}

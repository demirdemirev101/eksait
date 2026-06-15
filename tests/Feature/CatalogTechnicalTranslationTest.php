<?php

namespace Tests\Feature;

use App\Http\Resources\ProductAPIResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CatalogTermTranslator;
use App\Support\LocalizedContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CatalogTechnicalTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_terms_translate_through_provider_and_glossary(): void
    {
        $this->bootFakeTranslator();

        $product = Product::create([
            'name' => 'СВРЕДЛО ЗА МЕТАЛ A',
            'price' => 12.5,
            'stock' => true,
            'quantity' => 5,
            'description' => 'СВРЕДЛО ЗА ЛАМАРИНА',
        ]);

        $category = Category::create([
            'name' => 'СВРЕДЛА',
        ]);

        $product->categories()->attach($category);

        ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'Ф0.22 ЛАМАРИНА',
            'price' => 13.5,
            'quantity' => 2,
        ]);

        $payload = (new ProductAPIResource(
            $product->load(['categories', 'variants', 'images', 'relatedProducts'])
        ))->toArray(Request::create('/api/products?lang=en'));

        $this->assertSame('DRILL FOR METAL A', $payload['name']);
        $this->assertSame('DRILL FOR SHEET METAL', $payload['description']);
        $this->assertSame('DRILLS', $payload['categories'][0]['name']);
        $this->assertSame('Ø0.22 SHEET METAL', $payload['variants'][0]['size']);
    }

    public function test_catalog_terms_can_translate_through_google_provider(): void
    {
        config([
            'catalog_translation.provider' => 'google',
            'catalog_translation.google.base_url' => 'https://translation.googleapis.com',
            'catalog_translation.google.api_key' => 'test-google-key',
            'catalog_translation.google.endpoint' => '/language/translate/v2',
            'catalog_translation.cache_store' => 'array',
        ]);

        Cache::store('array')->flush();

        Http::fake(fn () => Http::response([
            'data' => [
                'translations' => [
                    ['translatedText' => 'Google &amp; Drill'],
                ],
            ],
        ], 200));

        $translated = app(CatalogTermTranslator::class)->translate('Ð¡Ð’Ð Ð•Ð”Ð›Ðž Ð—Ð ÐœÐ•Ð¢ÐÐ› A', 'en');

        $this->assertSame('Google & Drill', $translated);
    }

    public function test_grinder_family_terms_translate_using_catalog_dictionary(): void
    {
        $this->bootFakeTranslator();

        $product = Product::create([
            'name' => 'ШЛАЙФГРИФЕР ЕН',
            'price' => 9.9,
            'stock' => true,
            'quantity' => 3,
            'description' => 'АБРАЗИВ ЕР ЗА ПЛОСК ШЛАЙФ',
        ]);

        $category = Category::create([
            'name' => 'АБРАЗИВ',
        ]);

        $product->categories()->attach($category);

        ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'Ф0.8 ПЛОСК ШЛАЙФ',
            'price' => 11.1,
            'quantity' => 1,
        ]);

        $payload = (new ProductAPIResource(
            $product->load(['categories', 'variants', 'images', 'relatedProducts'])
        ))->toArray(Request::create('/api/products?lang=en'));

        $this->assertSame('MOUNTED POINT EN', $payload['name']);
        $this->assertSame('ABRASIVE ER FOR FLAT GRINDER', $payload['description']);
        $this->assertSame('ABRASIVE', $payload['categories'][0]['name']);
        $this->assertSame('Ø0.8 FLAT GRINDER', $payload['variants'][0]['size']);
    }

    public function test_german_locale_keeps_product_names_in_english(): void
    {
        $this->bootFakeTranslator();

        $product = Product::create([
            'name' => 'СВРЕДЛО ЗА МЕТАЛ A',
            'price' => 12.5,
            'stock' => true,
            'quantity' => 5,
            'description' => 'СВРЕДЛО ЗА ЛАМАРИНА',
        ]);

        $category = Category::create([
            'name' => 'СВРЕДЛА',
        ]);

        $product->categories()->attach($category);

        ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'Ф0.22 ЛАМАРИНА',
            'price' => 13.5,
            'quantity' => 2,
        ]);

        $payload = (new ProductAPIResource(
            $product->load(['categories', 'variants', 'images', 'relatedProducts'])
        ))->toArray(Request::create('/api/products?lang=de'));

        $this->assertSame('DRILL FOR METAL A', $payload['name']);
        $this->assertSame('DRILLS', $payload['categories'][0]['name']);
        $this->assertSame('Ø0.22 SHEET METAL', $payload['variants'][0]['size']);
        $this->assertSame('DRILL FOR METAL A', $payload['translations']['de']['name']);
    }

    public function test_catalog_uses_persisted_translations_without_provider_calls(): void
    {
        Http::fake();

        $product = Product::create([
            'name' => 'СВРЕДЛО ЗА МЕТАЛ A',
            'name_en' => 'DRILL FOR METAL A',
            'price' => 12.5,
            'stock' => true,
            'quantity' => 5,
            'description' => 'СВРЕДЛО ЗА ЛАМАРИНА',
            'description_en' => 'DRILL FOR SHEET METAL',
            'extra_information' => 'Допълнителна информация',
            'extra_information_en' => 'Extra information',
        ]);

        $category = Category::create([
            'name' => 'СВРЕДЛА',
            'name_en' => 'DRILLS',
        ]);

        $product->categories()->attach($category);

        ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'Ф0.22 ЛАМАРИНА',
            'size_en' => 'Ø0.22 SHEET METAL',
            'price' => 13.5,
            'quantity' => 2,
        ]);

        $payloadEn = (new ProductAPIResource(
            $product->load(['categories', 'variants', 'images', 'relatedProducts'])
        ))->toArray(Request::create('/api/products?lang=en'));

        $payloadDe = (new ProductAPIResource(
            $product->load(['categories', 'variants', 'images', 'relatedProducts'])
        ))->toArray(Request::create('/api/products?lang=de'));

        $this->assertSame('DRILL FOR METAL A', $payloadEn['name']);
        $this->assertSame('DRILL FOR METAL A', $payloadDe['name']);
        $this->assertSame('DRILLS', $payloadEn['categories'][0]['name']);
        $this->assertSame('DRILLS', $payloadDe['categories'][0]['name']);
        $this->assertSame('Ø0.22 SHEET METAL', $payloadDe['variants'][0]['size']);
        $this->assertSame('DRILL FOR METAL A', $payloadDe['translations']['de']['name']);

        Http::assertNothingSent();
    }

    public function test_legacy_fallback_transliterates_remaining_cyrillic_to_latin(): void
    {
        $translator = app(CatalogTermTranslator::class);

        $this->assertSame('KECHANA PUCK', $translator->translateOffline('КЕЧАНА ШАЙБА', 'en'));
        $this->assertSame('ABRASIVE SHEET', $translator->translateOffline('ШКУРКА НА ЛИСТ', 'en'));
        $this->assertSame('DIAMOND DRESSER CO', $translator->translateOffline('ДИАМАНТЕН ИЗРАВНИТЕЛ ЦО', 'en'));
    }

    public function test_flat_grinder_terms_prefer_english_over_transliteration(): void
    {
        $translator = app(CatalogTermTranslator::class);

        $this->assertSame(
            'ABRASIVE ER FOR FLAT GRINDER',
            $translator->translateOffline('АБРАЗИВ ЕР ЗА ПЛОСАК ШЛАЙФ', 'en')
        );
    }

    public function test_stale_english_translations_are_rebuilt_from_source_when_needed(): void
    {
        $product = Product::create([
            'name' => 'КЕЧАНА ШАЙБА',
            'name_en' => 'KECHANA SHAYBA',
            'price' => 12.5,
            'stock' => true,
            'quantity' => 5,
            'description' => 'ШКУРКА НА ЛИСТ',
            'description_en' => 'SHKURKA NA LIST',
        ]);

        $category = Category::create([
            'name' => 'ШКУРКА',
            'name_en' => 'SHKURKA',
        ]);

        $product->categories()->attach($category);

        $payload = (new ProductAPIResource(
            $product->load(['categories', 'variants', 'images', 'relatedProducts'])
        ))->toArray(Request::create('/api/products?lang=en'));

        $this->assertSame('KECHANA PUCK', $payload['name']);
        $this->assertSame('ABRASIVE SHEET', $payload['description']);
        $this->assertSame('ABRASIVE PAPER', $payload['categories'][0]['name']);
    }

    public function test_caliber_terms_prefer_dictionary_translations_over_transliteration(): void
    {
        $translator = app(CatalogTermTranslator::class);

        $this->assertSame('CALIBRES', $translator->translateOffline('КАЛИБРИ', 'en'));
        $this->assertSame('CALIBER BRACELET', $translator->translateOffline('КАЛИБЪР ГРИВНА', 'en'));
        $this->assertSame('CALIBER PROBE TR', $translator->translateOffline('КАЛИБЪР ПРОБКА ТР', 'en'));
        $this->assertSame('CALIBER SMOOTH', $translator->translateOffline('КАЛИБЪР ГЛАДАК', 'en'));
    }

    public function test_stale_caliber_transliterations_are_rebuilt_from_source_when_needed(): void
    {
        $product = Product::create([
            'name' => 'КАЛИБЪР ПРОБКА ТР',
            'name_en' => 'KALIBAR PROBKA TR',
            'price' => 12.5,
            'stock' => true,
            'quantity' => 5,
        ]);

        $category = Category::create([
            'name' => 'КАЛИБРИ',
            'name_en' => 'KALIBRI',
        ]);

        $product->categories()->attach($category);

        $payload = (new ProductAPIResource(
            $product->load(['categories', 'variants', 'images', 'relatedProducts'])
        ))->toArray(Request::create('/api/products?lang=en'));

        $this->assertSame('CALIBER PROBE TR', $payload['name']);
        $this->assertSame('CALIBRES', $payload['categories'][0]['name']);
    }

    public function test_new_technical_terms_translate_with_catalog_dictionary(): void
    {
        $translator = app(CatalogTermTranslator::class);

        $this->assertSame('RAIL', $translator->translateOffline('ШИНА', 'en'));
        $this->assertSame('CUT-OFF', $translator->translateOffline('ОТРЕЗНА', 'en'));
        $this->assertSame('LEFT', $translator->translateOffline('ЛЯВ', 'en'));
        $this->assertSame('RIGHT', $translator->translateOffline('ДЕСЕН', 'en'));
        $this->assertSame('CERAMIC', $translator->translateOffline('КЕРАМИЧНА', 'en'));
        $this->assertSame('THREAD', $translator->translateOffline('РЕЗБ', 'en'));
        $this->assertSame('THREADED', $translator->translateOffline('РЕЗБОВИ', 'en'));
        $this->assertSame('FOR INTERNAL THREAD', $translator->translateOffline('ЗА ВЪТР Р-БА', 'en'));
    }

    public function test_stale_new_technical_transliterations_are_rebuilt_from_source_when_needed(): void
    {
        $product = Product::create([
            'name' => 'ЗА ВЪТР Р-БА',
            'name_en' => 'FOR VATR R-BA',
            'price' => 12.5,
            'stock' => true,
            'quantity' => 5,
        ]);

        $payload = (new ProductAPIResource(
            $product->load(['categories', 'variants', 'images', 'relatedProducts'])
        ))->toArray(Request::create('/api/products?lang=en'));

        $this->assertSame('FOR INTERNAL THREAD', $payload['name']);
    }

    public function test_tap_terms_translate_with_catalog_dictionary(): void
    {
        $translator = app(CatalogTermTranslator::class);

        $this->assertSame('TAPS', $translator->translateOffline('МЕТЧИЦИ', 'en'));
        $this->assertSame('MACHINE TAP', $translator->translateOffline('МЕТЧИК МАШ', 'en'));
        $this->assertSame('HAND TAP', $translator->translateOffline('МЕТЧИК РЪЧЕН', 'en'));
        $this->assertSame('TAP', $translator->translateOffline('МЕТЧИК', 'en'));
    }

    public function test_stale_tap_transliterations_are_rebuilt_from_source_when_needed(): void
    {
        $product = Product::create([
            'name' => 'МЕТЧИК МАШ ТР',
            'name_en' => 'METCHIK MACHINE TR',
            'price' => 12.5,
            'stock' => true,
            'quantity' => 5,
        ]);

        $category = Category::create([
            'name' => 'МЕТЧИЦИ',
            'name_en' => 'METCHITSI',
        ]);

        $product->categories()->attach($category);

        $payload = (new ProductAPIResource(
            $product->load(['categories', 'variants', 'images', 'relatedProducts'])
        ))->toArray(Request::create('/api/products?lang=en'));

        $this->assertSame('MACHINE TAP TR', $payload['name']);
        $this->assertSame('TAPS', $payload['categories'][0]['name']);
    }

    public function test_die_and_cutter_terms_translate_with_catalog_dictionary(): void
    {
        $translator = app(CatalogTermTranslator::class);

        $this->assertSame('DIES', $translator->translateOffline('ПЛАШКИ', 'en'));
        $this->assertSame('DIE LEFT', $translator->translateOffline('ПЛАШКА ЛЯВА', 'en'));
        $this->assertSame('DIE INCH', $translator->translateOffline('ПЛАШКА ЦОЛОВА', 'en'));
        $this->assertSame('CUTTER FOR INTERNAL GROOVE', $translator->translateOffline('НОЖ ЗА ВЪТРЕШЕН КАНАЛ', 'en'));
        $this->assertSame('CUTTER FOR BLIND HOLE', $translator->translateOffline('НОЖ ЗА ГЛУХ ОТВОР', 'en'));
        $this->assertSame('SLOTTING CUTTER', $translator->translateOffline('ПРОРЕЗЕН НОЖ', 'en'));
        $this->assertSame('BORING BAR', $translator->translateOffline('БОРЩАНГА', 'en'));
        $this->assertSame('FINISHING CUTTER', $translator->translateOffline('НОЖ ЧИСТ', 'en'));
        $this->assertSame('HACKSAW BLADE', $translator->translateOffline('НОЖОВКА ЛИСТ', 'en'));
        $this->assertSame('GUILLOTINE CUTTER', $translator->translateOffline('НОЖ ГИЛОТИНА', 'en'));
    }

    public function test_stale_die_and_cutter_transliterations_are_rebuilt_from_source_when_needed(): void
    {
        $product = Product::create([
            'name' => 'НОЖ ЗА ВЪТРЕШЕН КАНАЛ',
            'name_en' => 'CUTTER FOR VATRESHEN KANAL',
            'price' => 12.5,
            'stock' => true,
            'quantity' => 5,
        ]);

        $category = Category::create([
            'name' => 'ПЛАШКИ',
            'name_en' => 'PLASHKI',
        ]);

        $product->categories()->attach($category);

        $payload = (new ProductAPIResource(
            $product->load(['categories', 'variants', 'images', 'relatedProducts'])
        ))->toArray(Request::create('/api/products?lang=en'));

        $this->assertSame('CUTTER FOR INTERNAL GROOVE', $payload['name']);
        $this->assertSame('DIES', $payload['categories'][0]['name']);
    }

    public function test_insert_shape_and_thread_terms_translate_with_catalog_dictionary(): void
    {
        $translator = app(CatalogTermTranslator::class);

        $this->assertSame('INSERT PENTAGONAL', $translator->translateOffline('ПЛАСТИНА ПЕТОАГАЛНА', 'en'));
        $this->assertSame('HEXAGONAL', $translator->translateOffline('ШЕСТОАГАЛНА', 'en'));
        $this->assertSame('PAD FOR THREADED INSERT', $translator->translateOffline('ПОДЛОЖКА ЗА РЕЗБОВА ПЛАСТИНА', 'en'));
        $this->assertSame('INSERT INTERNAL THREAD', $translator->translateOffline('ПЛАСТИНА ВЪТР Р-БА', 'en'));
        $this->assertSame('INSERT EXTERNAL THREAD', $translator->translateOffline('ПЛАСТИНА ВЪНШ Р-БА', 'en'));
        $this->assertSame('INSERT CIRCLIP GROOVE', $translator->translateOffline('ПЛАСТИНА ЗЕГЕРОВ КАНАЛ', 'en'));
        $this->assertSame('INSERT BRAZED', $translator->translateOffline('ПЛАСТИНА ЗАПОЯЕМА', 'en'));
        $this->assertSame('INSERT ROUND GROOVE BG', $translator->translateOffline('ПЛАСТИНА КРАГАЛ КАНАЛ BG', 'en'));
        $this->assertSame('INSERT GROOVING', $translator->translateOffline('ПЛАСТИНА КАНАЛНА', 'en'));
    }

    public function test_stale_insert_transliterations_are_rebuilt_from_source_when_needed(): void
    {
        $product = Product::create([
            'name' => 'ПЛАСТИНА ВЪТР Р-БА',
            'name_en' => 'INSERT VATR THREAD',
            'price' => 12.5,
            'stock' => true,
            'quantity' => 5,
        ]);

        $payload = (new ProductAPIResource(
            $product->load(['categories', 'variants', 'images', 'relatedProducts'])
        ))->toArray(Request::create('/api/products?lang=en'));

        $this->assertSame('INSERT INTERNAL THREAD', $payload['name']);
    }

    public function test_cobalt_blank_and_hard_cutter_terms_translate_with_catalog_dictionary(): void
    {
        $translator = app(CatalogTermTranslator::class);

        $this->assertSame('COBALT BLANK', $translator->translateOffline('ЗАГОТОВКА КОБАЛТ', 'en'));
        $this->assertSame('COBALT BLANK', $translator->translateOffline('ЗАГОТОВКА КОБАЛТОВА', 'en'));
        $this->assertSame('HARD CUTTER FOR GROOVE CXS-07', $translator->translateOffline('НОЖ ТВЪРД ЗА КАНАЛ CXS-07', 'en'));
        $this->assertSame('RAIL COBALT', $translator->translateOffline('ШИНА КОБАЛТ', 'en'));
    }

    public function test_stale_cobalt_and_hard_transliterations_are_rebuilt_from_source_when_needed(): void
    {
        $product = Product::create([
            'name' => 'ЗАГОТОВКА КОБАЛТОВА',
            'name_en' => 'ZAGOTOVKA KOBALTOVA',
            'price' => 12.5,
            'stock' => true,
            'quantity' => 5,
        ]);

        $payload = (new ProductAPIResource(
            $product->load(['categories', 'variants', 'images', 'relatedProducts'])
        ))->toArray(Request::create('/api/products?lang=en'));

        $this->assertSame('COBALT BLANK', $payload['name']);
    }

    public function test_direct_latin_transliterations_translate_with_catalog_dictionary(): void
    {
        $translator = app(CatalogTermTranslator::class);

        $this->assertSame('INSERT PENTAGONAL', $translator->translateOffline('INSERT PETAOGALNA', 'en'));
        $this->assertSame('PENTAGONAL', $translator->translateOffline('PETAOGALNA', 'en'));
        $this->assertSame('HEXAGONAL', $translator->translateOffline('SHESTOAGALNA', 'en'));
        $this->assertSame('INSERT INTERNAL THREAD ST', $translator->translateOffline('INSERT VATR THREAD ST', 'en'));
        $this->assertSame('INSERT INTERNAL THREAD', $translator->translateOffline('INSERT VATR THREAD', 'en'));
    }

    public function test_milling_terms_translate_with_technical_meaning(): void
    {
        $translator = app(CatalogTermTranslator::class);

        $this->assertSame('MILLING CUTTERS', $translator->translateOffline('ФРЕЗИ', 'en'));
        $this->assertSame('MILL 2 FLUTES TSO', $translator->translateOffline('МИЛ 2 ПЕРА ТСО', 'en'));
        $this->assertSame('MILL 3 FLUTES CARBIDE', $translator->translateOffline('МИЛ 3 ПЕРА ТВЪРДОСПЛАВНА', 'en'));
        $this->assertSame('MILL THREE-SIDED N', $translator->translateOffline('МИЛ ТРИСТР Н', 'en'));
        $this->assertSame('MILL ANGLE', $translator->translateOffline('МИЛ ЪГЛОВА', 'en'));
        $this->assertSame('ARBOR MILLING CUTTER SQUARE PL', $translator->translateOffline('ФРЕЗА ДОРН КВАДР ПЛ', 'en'));
        $this->assertSame('ARBOR MILLING CUTTER TRIANGULAR PL', $translator->translateOffline('ФРЕЗА ДОРН ТРИАГ ПЛ', 'en'));
        $this->assertSame('MILLING CUTTERS COBALT VARIOUS', $translator->translateOffline('ФРЕЗИ КОБАЛТ РАЗЛИЧНИ', 'en'));
    }

    public function test_stale_milling_transliterations_are_rebuilt_from_source_when_needed(): void
    {
        $product = Product::create([
            'name' => 'МИЛ 2 ПЕРА ТСО',
            'name_en' => 'MILL 2 PERA TSO',
            'price' => 12.5,
            'stock' => true,
            'quantity' => 5,
        ]);

        $category = Category::create([
            'name' => 'ФРЕЗИ',
            'name_en' => 'FREZI',
        ]);

        $product->categories()->attach($category);

        $payload = (new ProductAPIResource(
            $product->load(['categories', 'variants', 'images', 'relatedProducts'])
        ))->toArray(Request::create('/api/products?lang=en'));

        $this->assertSame('MILL 2 FLUTES TSO', $payload['name']);
        $this->assertSame('MILLING CUTTERS', $payload['categories'][0]['name']);
    }

    public function test_meter_collet_and_hand_tool_terms_translate_with_catalog_dictionary(): void
    {
        $translator = app(CatalogTermTranslator::class);

        $this->assertSame('ABRASIVE PAPER PER METER', $translator->translateOffline('ШКУРКА НА МЕТАР', 'en'));
        $this->assertSame('COLLET', $translator->translateOffline('ЦАНГА', 'en'));
        $this->assertSame('MOUNTED POINT FELT', $translator->translateOffline('ШЛАЙФГРИФЕР КЕЧЕ', 'en'));
        $this->assertSame('DIAMOND DISC GRIT', $translator->translateOffline('ДИАМАНТЕН ДИСК ЗЪРН.', 'en'));
        $this->assertSame('ARBOR ISO50 FOR FU320 EXTENDED', $translator->translateOffline('ДОРНИК ISO50 ЗА FU320 УДЪЛЖЕН', 'en'));
        $this->assertSame('MOTOR DC', $translator->translateOffline('ЕЛ ДВИГАТЕЛ ПРАВОТОКОВ', 'en'));
        $this->assertSame('ADJUSTABLE REAMER', $translator->translateOffline('РАЗДВИЖЕН РАЗВЕРТКА', 'en'));
        $this->assertSame('BALL REAMER', $translator->translateOffline('САЧМЕН РАЗВЕРТКА', 'en'));
        $this->assertSame('ARBOR REAMER', $translator->translateOffline('ДОРНИКОВ РАЗВЕРТКА', 'en'));
        $this->assertSame('WRENCH HEX', $translator->translateOffline('ГАЕЧЕН ШЕСТОСТЕН', 'en'));
        $this->assertSame('WRENCH STAR', $translator->translateOffline('ГАЕЧЕН ЗВЕЗДА', 'en'));
        $this->assertSame('BLIND WRENCH', $translator->translateOffline('СЛЯП ГАЕЧЕН', 'en'));
        $this->assertSame('PLIERS CUTTERS', $translator->translateOffline('КЛЕЩИ СЕКАЧКИ', 'en'));
        $this->assertSame('PLIERS COMBINATION', $translator->translateOffline('КЛЕЩИ КОМБИН', 'en'));
        $this->assertSame('CONTACTOR', $translator->translateOffline('КОНТАКТОР', 'en'));
        $this->assertSame('PUNCH', $translator->translateOffline('ЗАМБА', 'en'));
        $this->assertSame('DENTAL INSTRUMENTS', $translator->translateOffline('ЗЪБОЛЕКАРСКИ ИНСТРУМЕНТИ', 'en'));
    }

    public function test_additional_drill_and_tool_terms_translate_with_catalog_dictionary(): void
    {
        $translator = app(CatalogTermTranslator::class);

        $this->assertSame('MAGNETIC STAND', $translator->translateOffline('МАГНИТНА СТОЙКА', 'en'));
        $this->assertSame('VISE', $translator->translateOffline('МЕНГЕМЕ', 'en'));
        $this->assertSame('KNURLS ROLLERS', $translator->translateOffline('НАКАТКИ РОЛКИ', 'en'));
        $this->assertSame('SCREWDRIVER IMPACT', $translator->translateOffline('ОТВЕРКА УДАРНА', 'en'));
        $this->assertSame('BUSHING ADAPTER', $translator->translateOffline('ВТУЛКА ПРЕХОДНА', 'en'));
        $this->assertSame('SCREW CROSS-HEAD', $translator->translateOffline('ВИНТ КРЪСТАТ', 'en'));
        $this->assertSame('PRESSURE SWITCH', $translator->translateOffline('ПРЕСОСТАТ', 'en'));
        $this->assertSame('GLOVES LEATHER', $translator->translateOffline('РЪКАВИЦИ КОЖЕНИ', 'en'));
        $this->assertSame('CONNECTOR', $translator->translateOffline('СЪЕДИНИТЕЛ', 'en'));
        $this->assertSame('GLASS CUTTER', $translator->translateOffline('СТЪКЛОРЕЗ', 'en'));
        $this->assertSame('TEXTOLITE', $translator->translateOffline('ТЕКСТОЛИТ', 'en'));
        $this->assertSame('FACEPLATE', $translator->translateOffline('ПЛАНШАЙБА', 'en'));
        $this->assertSame('SCRIBER CARBIDE', $translator->translateOffline('ЧЕРТИЛКА ТВЪРДОСПЛАВНА', 'en'));
        $this->assertSame('SCRIBER COBALT', $translator->translateOffline('ЧЕРТИЛКА КОБАЛТ', 'en'));
        $this->assertSame('JAWS STRAIGHT', $translator->translateOffline('ЧЕЛЮСТИ ПРАВИ', 'en'));
        $this->assertSame('JAWS REVERSE', $translator->translateOffline('ЧЕЛЮСТИ ОБРАТНИ', 'en'));
        $this->assertSame('SPRINGS', $translator->translateOffline('ПРУЖИНКИ', 'en'));
        $this->assertSame('FOOT', $translator->translateOffline('КРАЧЕ', 'en'));
        $this->assertSame('SCRAPER', $translator->translateOffline('ШАБЪР', 'en'));
        $this->assertSame('FIBERGLASS', $translator->translateOffline('ФИБРОСТЪКЛО', 'en'));
        $this->assertSame('DIGITS', $translator->translateOffline('ЦИФРИ', 'en'));
        $this->assertSame('CLAMPS', $translator->translateOffline('ПРИТИСКАЧИ', 'en'));
        $this->assertSame('MILL FINGER', $translator->translateOffline('МИЛ ПАЛЦОВА', 'en'));
        $this->assertSame('ELECTRIC PANEL', $translator->translateOffline('ЕЛ ТАБЛО', 'en'));
        $this->assertSame('LONG', $translator->translateOffline('DALGO', 'en'));
        $this->assertSame('CHEHIYA', $translator->translateOffline('CHEHIYA', 'en'));
        $this->assertSame('LEFT', $translator->translateOffline('ЛЯВО', 'en'));
        $this->assertSame('1/2 INCH', $translator->translateOffline('1/2 ЦОЛ', 'en'));
        $this->assertSame('2 INCH', $translator->translateOffline('2 ЦОЛА', 'en'));
        $this->assertSame('1/2 12 THREADS', $translator->translateOffline('1/2 12 НАВИВКИ', 'en'));
    }

    public function test_stale_additional_tool_transliterations_are_rebuilt_from_source_when_needed(): void
    {
        $product = Product::create([
            'name' => 'МАГНИТНА СТОЙКА',
            'name_en' => 'MAGNITNA STOYKA',
            'price' => 12.5,
            'stock' => true,
            'quantity' => 5,
        ]);

        $payload = (new ProductAPIResource(
            $product->load(['categories', 'variants', 'images', 'relatedProducts'])
        ))->toArray(Request::create('/api/products?lang=en'));

        $this->assertSame('MAGNETIC STAND', $payload['name']);
    }

    public function test_stale_variant_transliterations_are_rebuilt_from_source_when_needed(): void
    {
        $product = Product::create([
            'name' => 'СВРЕДЛО ЦО',
            'price' => 12.5,
            'stock' => true,
            'quantity' => 5,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'Ø1.55 ЧЕХИЯ',
            'size_en' => 'Ø1.55 CHEHIYA',
            'price' => 13.5,
            'quantity' => 2,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'ДЪЛГО',
            'size_en' => 'DALGO',
            'price' => 14.5,
            'quantity' => 1,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'Ø1.0 ЛЯВО',
            'size_en' => 'Ø1.0 LYAVO',
            'price' => 15.5,
            'quantity' => 1,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'size' => '1/2 ЦОЛ',
            'size_en' => '1/2 TSOL',
            'price' => 16.5,
            'quantity' => 1,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'size' => '1/2 12 НАВИВКИ',
            'size_en' => '1/2 12 NAVIVKI',
            'price' => 17.5,
            'quantity' => 1,
        ]);

        $payload = (new ProductAPIResource(
            $product->load(['categories', 'variants', 'images', 'relatedProducts'])
        ))->toArray(Request::create('/api/products?lang=en'));

        $this->assertSame('Ø1.55 CHEHIYA', $payload['variants'][0]['size']);
        $this->assertSame('LONG', $payload['variants'][1]['size']);
        $this->assertSame('Ø1.0 LEFT', $payload['variants'][2]['size']);
        $this->assertSame('1/2 INCH', $payload['variants'][3]['size']);
        $this->assertSame('1/2 12 THREADS', $payload['variants'][4]['size']);
    }

    public function test_requested_locale_can_be_resolved_from_headers(): void
    {
        $request = Request::create('/api/products', 'GET', server: [
            'HTTP_X_LOCALE' => 'de',
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        ]);

        $this->assertSame('de', LocalizedContent::requestedLocale($request));

        $request = Request::create('/api/products', 'GET', server: [
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        ]);

        $this->assertSame('en', LocalizedContent::requestedLocale($request));
    }

    private function bootFakeTranslator(): void
    {
        config([
            'catalog_translation.provider' => 'libretranslate',
            'catalog_translation.base_url' => 'http://translator.test',
            'catalog_translation.cache_store' => 'array',
        ]);

        Http::fake(function ($request) {
            return Http::response([
                'translatedText' => $request->data()['q'] ?? '',
            ], 200);
        });
    }
}

<?php

namespace Tests\Feature;

use App\Http\Resources\ProductAPIResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\LocalizedContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
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

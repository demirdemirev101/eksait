<?php

namespace Tests\Feature;

use App\Http\Resources\ProductAPIResource;
use App\Models\Category;
use App\Models\HomeBanner;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CatalogLocalizationResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_resource_localizes_catalog_fields_and_keeps_stable_keys(): void
    {
        $this->bootFakeTranslator();

        $product = Product::create([
            'name' => 'СВРЕДЛО ЗА МЕТАЛ A',
            'price' => 12.5,
            'stock' => true,
            'quantity' => 5,
            'description' => 'СВРЕДЛО ЗА ЛАМАРИНА',
            'extra_information' => 'Допълнителна информация',
            'extra_information_en' => 'Extra information',
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

        $this->assertSame($product->id, $payload['id']);
        $this->assertSame($product->slug, $payload['slug']);
        $this->assertSame('DRILL FOR METAL A', $payload['name']);
        $this->assertSame('DRILL FOR SHEET METAL', $payload['description']);
        $this->assertSame('Extra information', $payload['extra_information']);
        $this->assertSame('DRILLS', $payload['categories'][0]['name']);
        $this->assertSame('Ø0.22 SHEET METAL', $payload['variants'][0]['size']);
        $this->assertSame('DRILL FOR METAL A', $payload['translations']['en']['name']);
        $this->assertArrayHasKey('price', $payload);
        $this->assertArrayHasKey('stock', $payload);
    }

    public function test_home_banner_endpoint_returns_fixed_products_link(): void
    {
        HomeBanner::create([
            'eyebrow' => 'Ново',
            'title' => 'Банер',
            'subtitle' => 'Подзаглавие',
            'button_text' => 'Виж',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/home-banner?lang=de');

        $response->assertOk();
        $response->assertJsonPath('items.0.eyebrow', 'Ново');
        $response->assertJsonPath('items.0.title', 'Банер');
        $response->assertJsonPath('items.0.subtitle', 'Подзаглавие');
        $response->assertJsonPath('items.0.button_text', 'Виж');
        $response->assertJsonPath('items.0.button_url', '/products');
        $response->assertJsonPath('home_banner_title', 'Банер');
        $response->assertJsonPath('home_banner_button_text', 'Виж');
        $response->assertJsonPath('home_banner_button_url', '/products');
    }

    private function bootFakeTranslator(): void
    {
        config([
            'catalog_translation.provider' => 'libretranslate',
            'catalog_translation.base_url' => 'http://translator.test',
            'catalog_translation.cache_store' => 'array',
        ]);

        Http::fake(function ($request) {
            $data = $request->data();
            $q = (string) ($data['q'] ?? '');
            $target = (string) ($data['target'] ?? '');
            $translated = $this->translateFakePayload($q, $target);

            return Http::response([
                'translatedText' => $translated,
            ], 200);
        });
    }

    private function translateFakePayload(string $text, string $target): string
    {
        return match ($text . '|' . $target) {
            'Ново|de' => 'Neu',
            default => $text,
        };
    }
}

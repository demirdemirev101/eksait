<?php

namespace Tests\Feature;

use App\Http\Resources\ProductAPIResource;
use App\Models\Category;
use App\Models\HomeBanner;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class CatalogLocalizationResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_resource_localizes_catalog_fields_and_keeps_stable_keys(): void
    {
        $product = Product::create([
            'name' => 'ФРЕЗА 2 ПЕРА',
            'name_en' => '2-flute end mill',
            'name_de' => 'Zweischneider Fraeser',
            'price' => 12.5,
            'stock' => true,
            'quantity' => 5,
            'description' => 'Базово описание',
            'description_en' => 'English description',
            'extra_information' => 'Допълнителна информация',
            'extra_information_en' => 'Extra information',
        ]);

        $category = Category::create([
            'name' => 'Инструменти',
            'name_en' => 'Tools',
            'name_de' => 'Werkzeuge',
        ]);

        $product->categories()->attach($category);

        ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'МАЛКА',
            'size_en' => 'Small',
            'size_de' => 'Klein',
            'price' => 13.5,
            'quantity' => 2,
        ]);

        $payload = (new ProductAPIResource(
            $product->load(['categories', 'variants', 'images', 'relatedProducts'])
        ))->toArray(Request::create('/api/products?lang=en'));

        $this->assertSame($product->id, $payload['id']);
        $this->assertSame($product->slug, $payload['slug']);
        $this->assertSame('2-flute end mill', $payload['name']);
        $this->assertSame('2-flute end mill', $payload['name_en']);
        $this->assertSame('English description', $payload['description']);
        $this->assertSame('Extra information', $payload['extra_information']);
        $this->assertSame('Tools', $payload['categories'][0]['name']);
        $this->assertSame('Small', $payload['variants'][0]['size']);
        $this->assertSame('ФРЕЗА 2 ПЕРА', $payload['translations']['bg']['name']);
        $this->assertSame('2-flute end mill', $payload['translations']['en']['name']);
        $this->assertArrayHasKey('price', $payload);
        $this->assertArrayHasKey('stock', $payload);
    }

    public function test_home_banner_endpoint_localizes_banner_text_and_keeps_legacy_fields(): void
    {
        HomeBanner::create([
            'eyebrow' => 'Ново',
            'eyebrow_en' => 'New',
            'eyebrow_de' => 'Neu',
            'title' => 'Банер',
            'title_en' => 'Banner',
            'title_de' => 'Banner DE',
            'subtitle' => 'Подзаглавие',
            'subtitle_en' => 'Subtitle',
            'subtitle_de' => 'Untertitel',
            'button_text' => 'Виж',
            'button_text_en' => 'View',
            'button_text_de' => 'Ansehen',
            'button_url' => '/catalog',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/home-banner?lang=de');

        $response->assertOk();
        $response->assertJsonPath('items.0.eyebrow', 'Neu');
        $response->assertJsonPath('items.0.title', 'Banner DE');
        $response->assertJsonPath('items.0.subtitle', 'Untertitel');
        $response->assertJsonPath('items.0.button_text', 'Ansehen');
        $response->assertJsonPath('items.0.eyebrow_en', 'New');
        $response->assertJsonPath('items.0.translations.en.title', 'Banner');
        $response->assertJsonPath('home_banner_title', 'Banner DE');
        $response->assertJsonPath('home_banner_button_text', 'Ansehen');
    }
}

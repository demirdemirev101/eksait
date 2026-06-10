<?php

namespace App\Http\Resources;

use App\Support\LocalizedContent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class ProductAPIResource extends JsonResource
{
    /**
     * Transform the resource into an array, ready for JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = LocalizedContent::requestedLocale($request);
        $variants = $this->whenLoaded('variants');
        $hasVariants = $variants instanceof Collection && $variants->isNotEmpty();
        $availableVariants = $hasVariants
            ? $variants->filter(fn ($variant) => (bool) $variant->stock && (int) $variant->quantity > 0)
            : collect();
        $relatedProducts = $this->whenLoaded('relatedProducts');

        return [
            'id' => $this->id,
            'name' => LocalizedContent::localizedValue($this->resource, 'name', $locale),
            'slug' => $this->slug,

            'price' => number_format((float) $this->price, 2, '.', ''),
            'sale_price' => $this->sale_price ? number_format((float) $this->sale_price, 2, '.', '') : null,
            'stock' => $hasVariants
                ? $availableVariants->isNotEmpty()
                : ((bool) $this->stock && (int) $this->quantity > 0),
            'quantity' => $hasVariants
                ? 0
                : max(0, (int) $this->quantity),
            'description' => LocalizedContent::localizedValue($this->resource, 'description', $locale),
            'extra_information' => LocalizedContent::localizedValue($this->resource, 'extra_information', $locale),
            'translations' => LocalizedContent::translations($this->resource, [
                'name',
                'description',
                'extra_information',
            ]),
            'categories' => $this->categories->map(
                fn ($category) => $this->mapCategory($category, $locale)
            )->values(),
            'variants' => $hasVariants
                ? $variants->map(fn ($variant) => $this->mapVariant($variant, $locale))->values()
                : [],
            'related_products' => $relatedProducts instanceof Collection
                ? $relatedProducts->map(fn ($related) => $this->mapRelatedProduct($related, $locale))->values()
                : [],
            'images' => $this->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'is_primary' => (bool) $image->is_primary,
                    'sort_order' => $image->sort_order,
                    'url' => asset('storage/' . $image->image_path),
                ];
            }),
        ];  
    }

    private function mapCategory(object $category, string $locale): array
    {
        return [
            'id' => $category->id,
            'name' => LocalizedContent::localizedValue($category, 'name', $locale),
            'slug' => $category->slug,
            'translations' => LocalizedContent::translations($category, ['name']),
        ];
    }

    private function mapVariant(object $variant, string $locale): array
    {
        return [
            'id' => $variant->id,
            'size' => LocalizedContent::localizedValue($variant, 'size', $locale),
            'price' => number_format((float) $variant->price, 2, '.', ''),
            'sale_price' => $variant->sale_price ? number_format((float) $variant->sale_price, 2, '.', '') : null,
            'stock' => (bool) $variant->stock && (int) $variant->quantity > 0,
            'quantity' => max(0, (int) $variant->quantity),
            'translations' => LocalizedContent::translations($variant, ['size']),
        ];
    }

    private function mapRelatedProduct(object $related, string $locale): array
    {
        $imagePath = $related->primaryImage?->image_path
            ?? $related->images?->first()?->image_path;

        return [
            'id' => $related->id,
            'name' => LocalizedContent::localizedValue($related, 'name', $locale),
            'slug' => $related->slug,
            'price' => number_format((float) $related->price, 2, '.', ''),
            'sale_price' => $related->sale_price ? number_format((float) $related->sale_price, 2, '.', '') : null,
            'stock' => (bool) $related->stock && (int) $related->quantity > 0,
            'categories' => $related->categories->map(
                fn ($category) => $this->mapCategory($category, $locale)
            )->values(),
            'translations' => LocalizedContent::translations($related, ['name']),
            'image_path' => $imagePath,
            'image_url' => $imagePath ? asset('storage/' . $imagePath) : null,
        ];
    }
}

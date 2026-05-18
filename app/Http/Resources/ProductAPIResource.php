<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductAPIResource extends JsonResource
{
    /**
     * Transform the resource into an array, ready for JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $variants = $this->whenLoaded('variants');
        $hasVariants = $variants instanceof \Illuminate\Support\Collection && $variants->isNotEmpty();
        $availableVariants = $hasVariants
            ? $variants->filter(fn ($variant) => (bool) $variant->stock && (int) $variant->quantity > 0)
            : collect();
        $relatedProducts = $this->whenLoaded('relatedProducts');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,

            'price' => number_format((float) $this->price, 2, '.', ''),
            'sale_price' => $this->sale_price ? number_format((float) $this->sale_price, 2, '.', '') : null,
            'stock' => $hasVariants
                ? $availableVariants->isNotEmpty()
                : ((bool) $this->stock && (int) $this->quantity > 0),
            'quantity' => $hasVariants
                ? (int) $availableVariants->sum('quantity')
                : max(0, (int) $this->quantity),
            'description' => $this->description,
            'extra_information' => $this->extra_information,
            'categories' => $this->categories,
            'variants' => $hasVariants
                ? $variants->map(fn ($variant) => [
                    'id' => $variant->id,
                    'size' => $variant->size,
                    'price' => number_format((float) $variant->price, 2, '.', ''),
                    'sale_price' => $variant->sale_price ? number_format((float) $variant->sale_price, 2, '.', '') : null,
                    'stock' => (bool) $variant->stock && (int) $variant->quantity > 0,
                    'quantity' => max(0, (int) $variant->quantity),
                    'weight' => $variant->weight !== null ? number_format((float) $variant->weight, 2, '.', '') : null,
                    'width' => $variant->width !== null ? number_format((float) $variant->width, 2, '.', '') : null,
                    'height' => $variant->height !== null ? number_format((float) $variant->height, 2, '.', '') : null,
                    'length' => $variant->length !== null ? number_format((float) $variant->length, 2, '.', '') : null,
                ])->values()
                : [],
            'related_products' => $relatedProducts instanceof \Illuminate\Support\Collection
                ? $relatedProducts->map(function ($related) {
                    $imagePath = $related->primaryImage?->image_path
                        ?? $related->images?->first()?->image_path;

                    return [
                        'id' => $related->id,
                        'name' => $related->name,
                        'slug' => $related->slug,
                        'price' => number_format((float) $related->price, 2, '.', ''),
                        'sale_price' => $related->sale_price ? number_format((float) $related->sale_price, 2, '.', '') : null,
                        'stock' => (bool) $related->stock && (int) $related->quantity > 0,
                        'image_path' => $imagePath,
                        'image_url' => $imagePath ? asset('storage/' . $imagePath) : null,
                    ];
                })->values()
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
}

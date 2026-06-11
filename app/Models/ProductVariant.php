<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'size',
        'size_en',
        'size_de',
        'price',
        'sale_price',
        'stock',
        'quantity',
        'weight',
        'width',
        'height',
        'length',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'stock' => 'boolean',
        'quantity' => 'integer',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'length' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function setSizeAttribute(?string $value): void
    {
        $this->attributes['size'] = $value === null ? null : Str::upper(trim($value));
    }

    protected static function booted(): void
    {
        static::saving(function (self $variant): void {
            $variant->quantity = max(0, (int) $variant->quantity);
            $variant->stock = $variant->quantity > 0;
        });

        static::saved(function (self $variant): void {
            $variant->syncParentStockState();
        });

        static::deleted(function (self $variant): void {
            $variant->syncParentStockState();
        });
    }

    private function syncParentStockState(): void
    {
        $product = $this->product;

        if (! $product) {
            return;
        }

        $product->forceFill([
            'quantity' => 0,
            'stock' => $product->variants()->where('quantity', '>', 0)->exists(),
        ])->saveQuietly();
    }
}

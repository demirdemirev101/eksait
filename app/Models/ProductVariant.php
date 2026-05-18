<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'size',
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

    protected static function booted(): void
    {
        static::saving(function (self $variant): void {
            // Quantity cannot be negative.
            $variant->quantity = max(0, (int) $variant->quantity);

            // If quantity is zero, variant must be out of stock.
            // Keep manual override possible for quantity > 0.
            if ($variant->quantity === 0) {
                $variant->stock = false;
            }
        });
    }
}

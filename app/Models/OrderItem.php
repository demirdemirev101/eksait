<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OrderItem extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'price',
        'quantity',
        'total',
    ];

    /**
     * Define a belongs-to relationship to the Order model, indicating that each OrderItem is associated with a single Order.
     */
    public function order() : BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    
    /**
     * Define a belongs-to relationship to the Product model, indicating that each OrderItem is associated with a single Product.
     */
    public function product() : BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function getSnapshotProductNameAttribute(): string
    {
        $productName = (string) ($this->product_name ?? '');

        if (! $this->product_variant_id || $productName === '') {
            return $productName;
        }

        return Str::beforeLast($productName, ' - ');
    }

    public function getSnapshotVariantNameAttribute(): ?string
    {
        $productName = (string) ($this->product_name ?? '');

        if (! $this->product_variant_id || $productName === '' || ! str_contains($productName, ' - ')) {
            return null;
        }

        $variantName = Str::afterLast($productName, ' - ');

        return $variantName !== '' ? $variantName : null;
    }

}

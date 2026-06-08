<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Product extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'name_en',
        'name_de',
        'price',
        'sale_price',
        'stock',
        'slug',
        'description',
        'description_en',
        'description_de',
        'extra_information',
        'extra_information_en',
        'extra_information_de',
        'quantity',
        'weight',
        'width',
        'height',
        'length',
    ];

    /**
     * Cast price and sale_price to decimal with 2 decimal places for consistent formatting.
     */
    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'boolean',
        'sale_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'length' => 'decimal:2',
    ];

    public function setNameAttribute(?string $value): void
    {
        $this->attributes['name'] = $value === null ? null : Str::upper(trim($value));
    }

    /**
     * Define a many-to-many relationship for categories.
     */
    public function categories() : BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Define a many-to-many relationship for related products.
     */
    public function relatedProducts() : BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_related', 'product_id', 'related_product_id');
    }

    /**
     * Override the default products relationship to point to relatedProducts.
     */
    public function products(): BelongsToMany
    {
        return $this->relatedProducts();
    }

    /**
     * Define a one-to-many relationship for product images, ordered by sort_order.
     */
    public function images() : HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Define a one-to-one relationship for the primary product image, filtered by is_primary flag.
     */
    public function primaryImage() : HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /**
     * Define a static boot method to handle slug generation.
     */
    public static function booted()
    {
        static::saving(function (Product $product) {
            if ($product->exists && $product->variants()->exists()) {
                $product->quantity = 0;
                $product->stock = $product->variants()->where('quantity', '>', 0)->exists();
            } else {
                $product->quantity = max(0, (int) ($product->quantity ?? 0));
                $product->stock = $product->quantity > 0;
            }

            if ($product->isDirty('name')) {
                $baseSlug = Str::slug($product->name);
                $slug = $baseSlug;
                $count = 1;

                while (
                    static::where('slug', $slug)
                        ->where('id', '!=', $product->id ?? 0)
                        ->exists()
                ) {
                    $slug = $baseSlug . '-' . ++$count;
                }

                $product->slug = $slug;
            }
        });
    }

    /**
     * Define a one-to-many relationship for product variants.
     */
    public function variants() : HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'eyebrow',
        'title',
        'subtitle',
        'button_text',
        'button_url',
        'image',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $banner): void {
            if ((int) ($banner->sort_order ?? 0) > 0) {
                return;
            }

            $banner->sort_order = ((int) static::query()->max('sort_order')) + 1;
        });
    }
}

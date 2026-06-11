<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'eyebrow',
        'eyebrow_en',
        'eyebrow_de',
        'title',
        'title_en',
        'title_de',
        'subtitle',
        'subtitle_en',
        'subtitle_de',
        'button_text',
        'button_text_en',
        'button_text_de',
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

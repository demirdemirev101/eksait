<?php

namespace App\Http\Controllers;

use App\Models\HomeBanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class HomeBannerController extends Controller
{
    public function show(): JsonResponse
    {
        $items = HomeBanner::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (HomeBanner $banner): array {
                return [
                    'id' => $banner->id,
                    'eyebrow' => $banner->eyebrow,
                    'title' => $banner->title,
                    'subtitle' => $banner->subtitle,
                    'button_text' => $banner->button_text,
                    'button_url' => $banner->button_url,
                    'image' => $banner->image,
                    'image_url' => $banner->image ? Storage::disk('public')->url($banner->image) : null,
                    'sort_order' => $banner->sort_order,
                ];
            })
            ->values();

        $first = $items->first();

        return response()->json([
            'items' => $items,
            // Backward-compatible fields from the first active banner.
            'home_banner_eyebrow' => $first['eyebrow'] ?? null,
            'home_banner_title' => $first['title'] ?? null,
            'home_banner_subtitle' => $first['subtitle'] ?? null,
            'home_banner_button_text' => $first['button_text'] ?? null,
            'home_banner_button_url' => $first['button_url'] ?? null,
            'home_banner_image' => $first['image'] ?? null,
            'home_banner_image_url' => $first['image_url'] ?? null,
        ]);
    }
}

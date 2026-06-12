<?php

namespace App\Http\Controllers;

use App\Models\HomeBanner;
use App\Support\LocalizedContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeBannerController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $locale = LocalizedContent::requestedLocale($request);

        $items = HomeBanner::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (HomeBanner $banner) use ($locale): array {
                return [
                    'id' => $banner->id,
                    'eyebrow' => LocalizedContent::localizedValue($banner, 'eyebrow', $locale),
                    'title' => LocalizedContent::localizedValue($banner, 'title', $locale),
                    'subtitle' => LocalizedContent::localizedValue($banner, 'subtitle', $locale),
                    'button_text' => LocalizedContent::localizedValue($banner, 'button_text', $locale),
                    'translations' => LocalizedContent::translations($banner, [
                        'eyebrow',
                        'title',
                        'subtitle',
                        'button_text',
                    ]),
                    'button_url' => '/products',
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
            'home_banner_button_url' => '/products',
            'home_banner_image_url' => $first['image_url'] ?? null,
        ]);
    }
}

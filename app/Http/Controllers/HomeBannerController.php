<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class HomeBannerController extends Controller
{
    public function show(): JsonResponse
    {
        $settings = Setting::current();
        $imagePath = $settings->home_banner_image;

        return response()->json([
            'home_banner_eyebrow' => $settings->home_banner_eyebrow,
            'home_banner_title' => $settings->home_banner_title,
            'home_banner_subtitle' => $settings->home_banner_subtitle,
            'home_banner_button_text' => $settings->home_banner_button_text,
            'home_banner_button_url' => $settings->home_banner_button_url,
            'home_banner_image' => $imagePath,
            'home_banner_image_url' => $imagePath ? Storage::disk('public')->url($imagePath) : null,
        ]);
    }
}


<?php

namespace App\Support;

class PublicStorageUrl
{
    public static function for(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return route('public-storage.show', ['path' => $path]);
    }
}

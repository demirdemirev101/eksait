<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartSessionToken
{
    private const PATTERN = '/\A[A-Za-z0-9_-]{16,128}\z/';

    public static function fromRequest(Request $request, bool $generateWhenMissing = false): ?string
    {
        $sessionId = $request->input('session_id')
            ?? $request->input('sessionId')
            ?? $request->query('session_id')
            ?? $request->query('sessionId')
            ?? $request->header('X-Cart-Session-Id');

        if (is_scalar($sessionId) && trim((string) $sessionId) !== '') {
            return self::remember(self::validate(trim((string) $sessionId)));
        }

        $rememberedSessionId = Session::get('cart_session_id');

        if (is_string($rememberedSessionId) && trim($rememberedSessionId) !== '') {
            return self::remember(self::validate(trim($rememberedSessionId)));
        }

        if ($generateWhenMissing) {
            return self::remember(self::generate());
        }

        return null;
    }

    public static function generate(): string
    {
        return Str::random(48);
    }

    private static function remember(string $sessionId): string
    {
        Session::put('cart_session_id', $sessionId);

        return $sessionId;
    }

    private static function validate(string $sessionId): string
    {
        if (! preg_match(self::PATTERN, $sessionId)) {
            throw ValidationException::withMessages([
                'session_id' => 'The cart session id must be 16-128 characters and contain only letters, numbers, underscores, or dashes.',
            ]);
        }

        return $sessionId;
    }
}

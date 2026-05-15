<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class CartService
{
    protected Cart $cart;
    protected ?string $sessionId;

    /**
     * Accept an explicit session ID (passed from the controller via the query param).
     * Falls back to the Laravel session ID for web requests that don't send one.
     */
    public function __construct(?string $sessionId = null)
    {
        $this->sessionId = $sessionId ?: (Session::get('cart_session_id') ?: Session::getId());
        $this->cart = $this->resolveCart();
    }

    protected function resolveCart(): Cart
    {
        if (Auth::check()) {
            return Cart::firstOrCreate(['user_id' => Auth::id()]);
        }

        return Cart::firstOrCreate(['session_id' => $this->sessionId]);
    }

    public function add(Product $product, int $quantity = 1, ?ProductVariant $variant = null): void
    {
        DB::transaction(function () use ($product, $quantity, $variant) {
            $item = $this->cartItemQuery($product, $variant)->first();

            $totalQuantity = $quantity + (int) ($item?->quantity ?? 0);
            $this->ensureQuantityAvailable($product, $totalQuantity, $variant);

            $price = $this->priceFor($product, $variant);

            if ($item) {
                $item->quantity += $quantity;
                $item->total    = $item->quantity * $price;
                $item->save();
            } else {
                $this->cart->items()->create([
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'quantity'   => $quantity,
                    'price'      => $price,
                    'total'      => $quantity * $price,
                ]);
            }
        });
    }

    public function update(Product $product, int $quantity, ?ProductVariant $variant = null): void
    {
        if ($quantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => 'The quantity must be at least 1.',
            ]);
        }

        $this->ensureQuantityAvailable($product, $quantity, $variant);

        $price = $this->priceFor($product, $variant);

        $this->cartItemQuery($product, $variant)
            ->update([
                'quantity' => $quantity,
                'total'    => $quantity * $price,
            ]);
    }

    public function remove(Product $product, ?ProductVariant $variant = null): void
    {
        $this->cartItemQuery($product, $variant)->delete();
    }

    public function items()
    {
        return $this->cart->items()->with(['product', 'variant'])->get();
    }

    public function clear(): void
    {
        $this->cart->items()->delete();
    }

    public function subtotal(): float
    {
        return (float) $this->cart->items()->sum('total');
    }

    public function cart(): Cart
    {
        return $this->cart;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    /**
     * Merge a guest cart into the authenticated user's cart on login.
     * Call this after Auth::login() in your LoginController.
     */
    public function mergeGuestCartToUser(): void
    {
        if (!Auth::check()) {
            Log::warning('Guest cart merge skipped because no user is authenticated', [
                'session_id' => $this->sessionId,
            ]);
            return;
        }

        $guestCart = Cart::where('session_id', $this->sessionId)->first();
        $userCart  = Cart::where('user_id', Auth::id())->first();

        if (!$guestCart) {
            Log::info('Guest cart merge skipped because guest cart was not found', [
                'session_id' => $this->sessionId,
                'user_id' => Auth::id(),
            ]);
            return;
        }

        DB::transaction(function () use ($guestCart, $userCart) {
            if (!$userCart) {
                $guestCart->update([
                    'user_id'    => Auth::id(),
                    'session_id' => null,
                ]);
                return;
            }

            foreach ($guestCart->items as $guestItem) {
                $userItem = $userCart->items()
                    ->where('product_id', $guestItem->product_id)
                    ->when(
                        $guestItem->product_variant_id,
                        fn ($query) => $query->where('product_variant_id', $guestItem->product_variant_id),
                        fn ($query) => $query->whereNull('product_variant_id')
                    )
                    ->first();

                if ($userItem) {
                    $userItem->quantity += $guestItem->quantity;
                    $userItem->total     = $userItem->quantity * $userItem->price;
                    $userItem->save();
                } else {
                    $userCart->items()->create([
                        'product_id' => $guestItem->product_id,
                        'product_variant_id' => $guestItem->product_variant_id,
                        'quantity'   => $guestItem->quantity,
                        'price'      => $guestItem->price,
                        'total'      => $guestItem->total,
                    ]);
                }
            }

            $guestCart->items()->delete();
            $guestCart->delete();
        });

        Log::info("Merged guest cart (session_id: {$this->sessionId}) into user cart (user_id: " . Auth::id() . ")");
    }

    public function hasGuestCart(): bool
    {
        return Cart::where('session_id', $this->sessionId)->exists();
    }

    private function ensureQuantityAvailable(Product $product, int $quantity, ?ProductVariant $variant = null): void
    {
        $stockTarget = $variant ? $variant->refresh() : $product->refresh();

        if (! $stockTarget->stock || (int) $stockTarget->quantity <= 0) {
            throw ValidationException::withMessages([
                'product' => 'Продуктът не е наличен.',
            ]);
        }

        if ($quantity > (int) $stockTarget->quantity) {
            throw ValidationException::withMessages([
                'quantity' => "Налични са само {$stockTarget->quantity} бр.",
            ]);
        }
    }

    private function cartItemQuery(Product $product, ?ProductVariant $variant)
    {
        return $this->cart->items()
            ->where('product_id', $product->id)
            ->when(
                $variant,
                fn ($query) => $query->where('product_variant_id', $variant->id),
                fn ($query) => $query->whereNull('product_variant_id')
            );
    }

    private function priceFor(Product $product, ?ProductVariant $variant = null): float
    {
        return (float) ($variant
            ? ($variant->sale_price ?? $variant->price ?? 0)
            : ($product->sale_price ?? $product->price ?? 0));
    }
}

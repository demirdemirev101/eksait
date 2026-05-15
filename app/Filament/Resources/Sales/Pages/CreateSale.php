<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Exceptions\CheckoutException;
use App\Filament\Resources\Sales\SaleResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\StockService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    protected static ?string $title = 'New sale';

    protected static bool $canCreateAnother = false;

    public array $cart = [];

    public function getBreadcrumb(): string
    {
        return 'New sale';
    }

    public function addToCart(int $productId, int $quantity, ?int $variantId = null): void
    {
        if ($productId <= 0 || $quantity <= 0) {
            Notification::make()
                ->title('Select product and quantity')
                ->warning()
                ->send();

            return;
        }

        $product = Product::query()
            ->with('variants')
            ->select(['id', 'name', 'price', 'sale_price', 'stock', 'quantity'])
            ->find($productId);

        if (! $product) {
            Notification::make()
                ->title('Product not found')
                ->danger()
                ->send();

            return;
        }

        $variant = $this->resolveVariantForCart($product, $variantId);
        if ($product->variants->isNotEmpty() && ! $variant) {
            return;
        }

        $cartKey = $this->cartKey($product->id, $variant?->id);
        $stockTarget = $variant ?? $product;
        $newQuantity = ($this->cart[$cartKey]['quantity'] ?? 0) + $quantity;

        if (! $stockTarget->stock || $newQuantity > (int) $stockTarget->quantity) {
            Notification::make()
                ->title('Insufficient stock')
                ->body("Available quantity for {$this->snapshotName($product, $variant)}: {$stockTarget->quantity}.")
                ->warning()
                ->send();

            return;
        }

        $price = $this->priceFor($product, $variant);

        $this->cart[$cartKey] = [
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'name' => $this->snapshotName($product, $variant),
            'price' => $price,
            'quantity' => $newQuantity,
            'total' => $price * $newQuantity,
        ];
    }

    public function increaseCartItem(string $cartKey): void
    {
        if (! isset($this->cart[$cartKey])) {
            return;
        }

        $this->addToCart(
            productId: (int) $this->cart[$cartKey]['product_id'],
            quantity: 1,
            variantId: $this->cart[$cartKey]['product_variant_id'] ?? null,
        );
    }

    public function decreaseCartItem(string $cartKey): void
    {
        if (! isset($this->cart[$cartKey])) {
            return;
        }

        if ($this->cart[$cartKey]['quantity'] <= 1) {
            $this->removeCartItem($cartKey);
            return;
        }

        $this->cart[$cartKey]['quantity']--;
        $this->cart[$cartKey]['total'] = $this->cart[$cartKey]['price'] * $this->cart[$cartKey]['quantity'];
    }

    public function removeCartItem(string $cartKey): void
    {
        unset($this->cart[$cartKey]);
    }

    public function cartTotal(): float
    {
        return array_reduce(
            $this->cart,
            fn (float $total, array $item): float => $total + (float) $item['total'],
            0.0,
        );
    }

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return DB::transaction(function () use ($data): Order {
                $items = collect($this->cart)
                    ->map(fn (array $item): array => [
                        'product_id' => (int) $item['product_id'],
                        'product_variant_id' => $item['product_variant_id'] ?? null,
                        'quantity' => (int) $item['quantity'],
                    ])
                    ->values();

                if ($items->isEmpty()) {
                    throw new CheckoutException('Add at least one product.', 422);
                }

                $products = Product::query()
                    ->with('variants')
                    ->whereIn('id', $items->pluck('product_id'))
                    ->get()
                    ->keyBy('id');
                $variants = ProductVariant::query()
                    ->whereIn('id', $items->pluck('product_variant_id')->filter()->all())
                    ->get()
                    ->keyBy('id');

                $subtotal = 0.0;

                foreach ($items as $item) {
                    $product = $products->get($item['product_id']);

                    if (! $product) {
                        throw new CheckoutException('Selected product no longer exists.', 422);
                    }

                    $variant = $this->variantForItem($product, $variants, $item['product_variant_id'] ?? null);
                    $subtotal += $this->priceFor($product, $variant) * $item['quantity'];
                }

                $order = Order::create([
                    'source' => 'panel',
                    'customer_name' => trim((string) ($data['customer_name'] ?? '')) ?: 'Walk-in customer',
                    'customer_email' => trim((string) ($data['customer_email'] ?? '')) ?: null,
                    'customer_phone' => trim((string) ($data['customer_phone'] ?? '')) ?: null,
                    'shipping_address' => 'In-store sale',
                    'shipping_city' => 'In-store sale',
                    'status' => 'completed',
                    'subtotal' => $subtotal,
                    'shipping_price' => 0,
                    'total' => $subtotal,
                    'payment_method' => $data['payment_method'],
                    'payment_status' => 'paid',
                    'notes' => trim((string) ($data['notes'] ?? '')) ?: null,
                ]);

                $stockService = app(StockService::class);

                foreach ($items as $item) {
                    $product = $products->get($item['product_id']);
                    $variant = $this->variantForItem($product, $variants, $item['product_variant_id'] ?? null);
                    $quantity = $item['quantity'];
                    $price = $this->priceFor($product, $variant);

                    if ($variant) {
                        $stockService->reserveVariant($variant, $quantity);
                    } else {
                        $stockService->reserve($product, $quantity);
                    }

                    $order->items()->create([
                        'product_id' => $product->id,
                        'product_variant_id' => $variant?->id,
                        'product_name' => $this->snapshotName($product, $variant),
                        'price' => $price,
                        'quantity' => $quantity,
                        'total' => $price * $quantity,
                    ]);
                }

                return $order;
            });
        } catch (CheckoutException $e) {
            Notification::make()
                ->title('Sale cannot be completed')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw new Halt();
        }
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Sale created successfully')
            ->success();
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Complete sale');
    }

    protected function getRedirectUrl(): string
    {
        return SaleResource::getUrl('index');
    }

    private function resolveVariantForCart(Product $product, ?int $variantId): ?ProductVariant
    {
        if (! $variantId) {
            if ($product->variants->isNotEmpty()) {
                Notification::make()
                    ->title('Select a variant')
                    ->warning()
                    ->send();
            }

            return null;
        }

        $variant = $product->variants->firstWhere('id', $variantId);

        if (! $variant) {
            Notification::make()
                ->title('Invalid variant')
                ->danger()
                ->send();
        }

        return $variant;
    }

    private function variantForItem(Product $product, Collection $variants, mixed $variantId): ?ProductVariant
    {
        if (empty($variantId)) {
            if ($product->variants->isNotEmpty()) {
                throw new CheckoutException("Select a variant for {$product->name}.", 422);
            }

            return null;
        }

        $variant = $variants->get($variantId);

        if (! $variant || $variant->product_id !== $product->id) {
            throw new CheckoutException("Invalid variant for {$product->name}.", 422);
        }

        return $variant;
    }

    private function priceFor(Product $product, ?ProductVariant $variant = null): float
    {
        return (float) ($variant
            ? ($variant->sale_price ?: $variant->price ?: 0)
            : ($product->sale_price ?: $product->price ?: 0));
    }

    private function snapshotName(Product $product, ?ProductVariant $variant = null): string
    {
        return $variant?->size
            ? "{$product->name} - {$variant->size}"
            : $product->name;
    }

    private function cartKey(int $productId, ?int $variantId = null): string
    {
        return $productId . ':' . ($variantId ?? 'none');
    }
}

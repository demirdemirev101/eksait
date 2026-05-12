<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Exceptions\CheckoutException;
use App\Filament\Resources\Sales\SaleResource;
use App\Models\Order;
use App\Models\Product;
use App\Services\StockService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    protected static ?string $title = 'Нова продажба';

    protected static bool $canCreateAnother = false;

    public array $cart = [];

    public function getBreadcrumb(): string
    {
        return 'Нова продажба';
    }

    public function addToCart(int $productId, int $quantity): void
    {
        if ($productId <= 0 || $quantity <= 0) {
            Notification::make()
                ->title('Изберете продукт и количество')
                ->warning()
                ->send();

            return;
        }

        $product = Product::query()
            ->select(['id', 'name', 'price', 'sale_price', 'stock', 'quantity'])
            ->find($productId);

        if (! $product) {
            Notification::make()
                ->title('Продуктът не е намерен')
                ->danger()
                ->send();

            return;
        }

        $newQuantity = ($this->cart[$productId]['quantity'] ?? 0) + $quantity;

        if ($product->stock && $newQuantity > $product->quantity) {
            Notification::make()
                ->title('Недостатъчна наличност')
                ->body("Налично количество за {$product->name}: {$product->quantity}.")
                ->warning()
                ->send();

            return;
        }

        $price = $this->priceFor($product);

        $this->cart[$productId] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => $price,
            'quantity' => $newQuantity,
            'total' => $price * $newQuantity,
        ];
    }

    public function increaseCartItem(int $productId): void
    {
        $this->addToCart($productId, 1);
    }

    public function decreaseCartItem(int $productId): void
    {
        if (! isset($this->cart[$productId])) {
            return;
        }

        if ($this->cart[$productId]['quantity'] <= 1) {
            $this->removeCartItem($productId);
            return;
        }

        $this->cart[$productId]['quantity']--;
        $this->cart[$productId]['total'] = $this->cart[$productId]['price'] * $this->cart[$productId]['quantity'];
    }

    public function removeCartItem(int $productId): void
    {
        unset($this->cart[$productId]);
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
                        'quantity' => (int) $item['quantity'],
                    ])
                    ->values();

                if ($items->isEmpty()) {
                    throw new CheckoutException('Добавете поне един продукт.', 422);
                }

                $products = Product::query()
                    ->whereIn('id', $items->pluck('product_id'))
                    ->get()
                    ->keyBy('id');

                $subtotal = 0.0;

                foreach ($items as $item) {
                    $product = $products->get($item['product_id']);

                    if (! $product) {
                        throw new CheckoutException('Избран продукт вече не съществува.', 422);
                    }

                    $subtotal += $this->priceFor($product) * $item['quantity'];
                }

                $order = Order::create([
                    'source' => 'panel',
                    'customer_name' => trim((string) ($data['customer_name'] ?? '')) ?: 'Клиент на място',
                    'customer_email' => trim((string) ($data['customer_email'] ?? '')) ?: null,
                    'customer_phone' => trim((string) ($data['customer_phone'] ?? '')) ?: null,
                    'shipping_address' => 'Продажба на място',
                    'shipping_city' => 'Продажба на място',
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
                    $quantity = $item['quantity'];
                    $price = $this->priceFor($product);

                    $stockService->reserve($product, $quantity);

                    $order->items()->create([
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'price' => $price,
                        'quantity' => $quantity,
                        'total' => $price * $quantity,
                    ]);
                }

                return $order;
            });
        } catch (CheckoutException $e) {
            Notification::make()
                ->title('Продажбата не може да бъде завършена')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw new Halt();
        }
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Продажбата е създадена успешно')
            ->success();
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Завърши продажбата');
    }

    protected function getRedirectUrl(): string
    {
        return SaleResource::getUrl('index');
    }

    private function priceFor(Product $product): float
    {
        return (float) ($product->sale_price ?: $product->price ?: 0);
    }
}

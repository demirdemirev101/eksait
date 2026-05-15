<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderItemService
{
    public function __construct(protected OrderService $orderService) {}

    public function create(array $data): OrderItem
    {
        return DB::transaction(function () use ($data) {
            $product = Product::query()
                ->with('variants')
                ->lockForUpdate()
                ->findOrFail($data['product_id']);
            $variant = $this->resolveVariant($product, $data['product_variant_id'] ?? null);
            $quantity = (int) ($data['quantity'] ?? 1);

            $this->decrementStock($variant ?? $product, $quantity);

            $price = $this->priceFor($product, $variant);
            $orderItem = OrderItem::create([
                'order_id' => $data['order_id'],
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'product_name' => $this->snapshotName($product, $variant),
                'price' => $price,
                'quantity' => $quantity,
                'total' => $price * $quantity,
            ]);

            $this->orderService->recalculateTotal($orderItem->order);

            return $orderItem;
        });
    }

    public function update(OrderItem $orderItem, array $data): OrderItem
    {
        return DB::transaction(function () use ($orderItem, $data) {
            $orderItem->loadMissing(['product', 'variant', 'order']);

            $oldTarget = $orderItem->variant ?? $orderItem->product;
            $oldQuantity = (int) $orderItem->quantity;

            $productId = (int) ($data['product_id'] ?? $orderItem->product_id);
            $product = Product::query()
                ->with('variants')
                ->lockForUpdate()
                ->findOrFail($productId);
            $variant = $this->resolveVariant($product, $data['product_variant_id'] ?? $orderItem->product_variant_id);
            $newQuantity = (int) ($data['quantity'] ?? $orderItem->quantity);
            $newTarget = $variant ?? $product;

            if ($oldTarget) {
                $this->incrementStock($oldTarget, $oldQuantity);
            }

            try {
                $this->decrementStock($newTarget, $newQuantity);
            } catch (\Throwable $e) {
                if ($oldTarget) {
                    $this->decrementStock($oldTarget, $oldQuantity);
                }

                throw $e;
            }

            $price = $this->priceFor($product, $variant);

            $orderItem->fill([
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'product_name' => $this->snapshotName($product, $variant),
                'price' => $price,
                'quantity' => $newQuantity,
                'total' => $price * $newQuantity,
            ]);
            $orderItem->save();

            $this->orderService->recalculateTotal($orderItem->order);

            return $orderItem;
        });
    }

    public function delete(OrderItem $orderItem): void
    {
        DB::transaction(function () use ($orderItem) {
            $orderItem->loadMissing(['product', 'variant', 'order']);

            $target = $orderItem->variant ?? $orderItem->product;
            if ($target) {
                $this->incrementStock($target, (int) $orderItem->quantity);
            }

            $order = $orderItem->order;
            $orderItem->delete();

            if ($order) {
                $this->orderService->recalculateTotal($order);
            }
        });
    }

    private function resolveVariant(Product $product, mixed $variantId): ?ProductVariant
    {
        if (empty($variantId)) {
            if ($product->variants->isNotEmpty()) {
                throw new \Exception('Please select a product variant.');
            }

            return null;
        }

        $variant = ProductVariant::query()
            ->where('product_id', $product->id)
            ->lockForUpdate()
            ->find($variantId);

        if (! $variant) {
            throw new \Exception('Invalid product variant.');
        }

        return $variant;
    }

    private function decrementStock(Model $target, int $quantity): void
    {
        $target->refresh();

        if (! $target->stock || (int) $target->quantity < $quantity) {
            throw new \Exception('Insufficient stock.');
        }

        $target->quantity = (int) $target->quantity - $quantity;
        if ($target->quantity <= 0) {
            $target->quantity = 0;
            $target->stock = false;
        }
        $target->save();
    }

    private function incrementStock(Model $target, int $quantity): void
    {
        $target->refresh();
        $target->quantity = (int) $target->quantity + $quantity;
        if ($target->quantity > 0) {
            $target->stock = true;
        }
        $target->save();
    }

    private function priceFor(Product $product, ?ProductVariant $variant = null): float
    {
        return (float) ($variant
            ? ($variant->sale_price ?? $variant->price ?? 0)
            : ($product->sale_price ?? $product->price ?? 0));
    }

    private function snapshotName(Product $product, ?ProductVariant $variant = null): string
    {
        return $variant?->size
            ? "{$product->name} - {$variant->size}"
            : $product->name;
    }
}

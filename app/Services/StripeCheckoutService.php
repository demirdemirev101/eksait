<?php

namespace App\Services;

use App\Models\Order;
use Stripe\StripeClient;


class StripeCheckoutService
{
    protected StripeClient $stripe;
    private const CURRENCY = 'eur';

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.sk'));
    }

    public function createSession(Order $order, ?string $sessionId = null)
    {
        $order->loadMissing('items');
        $lineItems = $order->items->map(fn ($item) => [
            'price_data' => [
                'currency' => self::CURRENCY,
                'product_data' => [
                    'name' => $item->product_name,
                ],
                'unit_amount' => $this->toMinorUnit((float) $item->price),
            ],
            'quantity' => $item->quantity,
        ])->values()->all();

        if ((float) $order->shipping_price > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => self::CURRENCY,
                    'product_data' => [
                        'name' => 'Shipping',
                    ],
                    'unit_amount' => $this->toMinorUnit((float) $order->shipping_price),
                ],
                'quantity' => 1,
            ];
        }

        $frontendUrl = $this->frontendUrl();

        return $this->stripe->checkout->sessions->create([
            'mode' => 'payment',
            'locale' => 'bg',
            'payment_method_types' => ['card'],
            'customer_email' => $order->customer_email,
            'line_items' => $lineItems,

            'success_url' => $frontendUrl . '/checkout/success?order_id=' . $order->id,
            'cancel_url' => $frontendUrl . '/checkout/cancel?order_id=' . $order->id,

            'metadata' => [
                'order_id' => (string) $order->id,
                'cart_session_id' => (string) $sessionId,
            ],

            'payment_intent_data' => [
                'metadata' => [
                    'order_id' => (string) $order->id,
                    'cart_session_id' => (string) $sessionId,
                ],
            ],
        ]);
    }

    private function toMinorUnit(float $amount): int
    {
        return (int) round($amount * 100);
    }

    private function frontendUrl(): string
    {
        $urls = array_filter(array_map(
            'trim',
            explode(',', (string) env('FRONTEND_URLS', ''))
        ));

        foreach ($urls as $url) {
            $host = parse_url($url, PHP_URL_HOST);

            if ($host && ! in_array($host, ['localhost', '127.0.0.1'], true)) {
                return rtrim($url, '/');
            }
        }

        $configuredUrl = (string) config('app.frontend_url', '');
        $configuredHost = parse_url($configuredUrl, PHP_URL_HOST);

        if ($configuredHost && ! in_array($configuredHost, ['localhost', '127.0.0.1'], true)) {
            return rtrim($configuredUrl, '/');
        }

        return 'http://192.168.1.102:5173';
    }
}

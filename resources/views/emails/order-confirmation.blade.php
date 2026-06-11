@extends('emails.layout')

@section('title', __('orders.mail.confirmation.title'))

@section('hero')
    <strong>{{ __('orders.mail.confirmation.hero_title', ['name' => $order->customer_name]) }}</strong><br>
    {{ __('orders.mail.confirmation.hero_text', ['order' => $order->id]) }}
@endsection

@section('content')
    <div class="section-title">{{ __('orders.mail.common.ordered_products') }}</div>

    <table class="items-table" role="presentation">
        <thead>
            <tr>
                <th>{{ __('orders.mail.common.product') }}</th>
                <th style="text-align: right;">{{ __('orders.mail.common.amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                @php
                    $itemName = $item->product_name
                        ?: trim(($item->product?->name ?? __('orders.mail.common.product_fallback')) . ($item->variant?->size ? ' - ' . $item->variant->size : ''));
                @endphp
                <tr>
                    <td>
                        <div class="item-name">{{ $itemName }}</div>
                        <div class="item-meta">{{ __('orders.mail.common.quantity_price', ['quantity' => $item->quantity, 'price' => number_format($item->price, 2)]) }}</div>
                    </td>
                    <td style="text-align: right; white-space: nowrap;">
                        {{ number_format($item->total, 2) }} €<br>
                        <span class="muted">{{ number_format($item->total * 1.9558, 2) }} лв.</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table" role="presentation">
        <tr class="first-row">
            <td>{{ __('orders.mail.common.products') }}</td>
            <td style="text-align: right;">
                {{ number_format($order->subtotal, 2) }} €<br>
                <span class="muted">{{ number_format($order->subtotal * 1.9558, 2) }} лв.</span>
            </td>
        </tr>
        <tr>
            <td>{{ __('orders.mail.common.shipping') }}</td>
            <td style="text-align: right;">
                {{ number_format($order->shipping_price, 2) }} €<br>
                <span class="muted">{{ number_format($order->shipping_price * 1.9558, 2) }} лв.</span>
            </td>
        </tr>
        <tr class="total-row">
            <td>{{ __('orders.mail.common.total') }}</td>
            <td style="text-align: right;">
                {{ number_format($order->total, 2) }} €<br>
                <span class="muted">{{ number_format($order->total * 1.9558, 2) }} лв.</span>
            </td>
        </tr>
    </table>

    @if ($order->payment_method === 'bank_transfer')
        <div class="section-title">{{ __('orders.mail.confirmation.bank_transfer.section') }}</div>
        <table class="dark-box" role="presentation">
            <tr><td colspan="2" class="dark-title">{{ __('orders.mail.confirmation.bank_transfer.title') }}</td></tr>
            <tr><td class="dark-label">{{ __('orders.mail.confirmation.bank_transfer.recipient') }}</td><td>{{ config('services.bank_transfer.company_name') }}</td></tr>
            <tr><td class="dark-label">IBAN</td><td>{{ config('services.bank_transfer.iban') }}</td></tr>
            <tr><td class="dark-label">{{ __('orders.mail.confirmation.bank_transfer.bank') }}</td><td>{{ config('services.bank_transfer.bank_name') }}</td></tr>
            <tr><td class="dark-label">BIC</td><td>{{ config('services.bank_transfer.bic') }}</td></tr>
            <tr><td class="dark-label">{{ __('orders.mail.confirmation.bank_transfer.amount') }}</td><td>{{ number_format($order->total, 2) }} {{ config('services.bank_transfer.currency') }} / {{ number_format($order->total * 1.9558, 2) }} BGN</td></tr>
            <tr><td class="dark-label">{{ __('orders.mail.confirmation.bank_transfer.reference') }}</td><td>{{ __('orders.mail.confirmation.bank_transfer.reference_value', ['order' => $order->id]) }}</td></tr>
            <tr><td colspan="2" class="dark-note">{{ __('orders.mail.confirmation.bank_transfer.note') }}</td></tr>
        </table>
    @endif

    @php
        $shippingMethodLabels = [
            'address' => __('orders.mail.shipping_methods.address'),
            'office' => __('orders.mail.shipping_methods.office'),
            'apm' => __('orders.mail.shipping_methods.apm'),
        ];

        $shippingCityLine = trim(implode(' ', array_filter([
            $order->shipping_postcode,
            $order->shipping_city,
        ])));

        $shippingLines = $order->shipping_method === 'address'
            ? [
                $order->customer_name,
                $order->customer_phone ? __('orders.mail.shipping.phone', ['phone' => $order->customer_phone]) : null,
                $order->shipping_address,
                $shippingCityLine,
            ]
            : [
                $order->customer_name,
                $order->customer_phone ? __('orders.mail.shipping.phone', ['phone' => $order->customer_phone]) : null,
                $order->econt_office_address,
                $order->econt_office_code ? __('orders.mail.shipping.code', ['code' => $order->econt_office_code]) : null,
                $shippingCityLine ? __('orders.mail.shipping.city', ['city' => $shippingCityLine]) : null,
            ];

        $shippingLines = array_values(array_filter($shippingLines, fn ($line) => filled($line)));
    @endphp

    <div class="section-title">{{ $shippingMethodLabels[$order->shipping_method] ?? __('orders.mail.shipping_methods.address') }}</div>
    <div class="info-box">
        @forelse ($shippingLines as $line)
            {{ $line }}@if (! $loop->last)<br>@endif
        @empty
            {{ __('orders.mail.shipping.missing') }}
        @endforelse
    </div>

    <p style="margin: 0; color: #555555; font-size: 14px; line-height: 1.7;">
        {{ __('orders.mail.confirmation.support') }}
    </p>
@endsection

@extends('emails.layout')

@section('title', __('orders.mail.cancelled.title'))

@section('hero')
    <strong>{{ __('orders.mail.cancelled.hero_title', ['name' => $order->customer_name]) }}</strong><br>
    {{ __('orders.mail.cancelled.hero_text', ['order' => $order->id]) }}
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
            <td>{{ __('orders.mail.common.shipping') }}</td>
            <td style="text-align: right;">{{ number_format($order->shipping_price, 2) }} €<br><span class="muted">{{ number_format($order->shipping_price * 1.9558, 2) }} лв.</span></td>
        </tr>
        <tr class="total-row">
            <td>{{ __('orders.mail.common.total') }}</td>
            <td style="text-align: right;">{{ number_format($order->total, 2) }} €<br><span class="muted">{{ number_format($order->total * 1.9558, 2) }} лв.</span></td>
        </tr>
    </table>

    <p style="margin: 0; color: #555555; font-size: 14px; line-height: 1.7;">
        {{ __('orders.mail.cancelled.support') }}
    </p>
@endsection

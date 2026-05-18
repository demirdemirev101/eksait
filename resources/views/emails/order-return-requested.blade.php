@extends('emails.layout')

@section('title', 'Заявено връщане на поръчка')

@section('hero')
    <strong>Здравей, {{ $order->customer_name }}.</strong><br>
    Получихме заявка за връщане на поръчка <strong>#{{ $order->id }}</strong>.
@endsection

@section('content')
    <div class="section-title">Поръчани продукти</div>

    <table class="items-table" role="presentation">
        <thead>
            <tr>
                <th>Продукт</th>
                <th style="text-align: right;">Сума</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                @php
                    $itemName = $item->product_name
                        ?: trim(($item->product?->name ?? 'Продукт') . ($item->variant?->size ? ' - ' . $item->variant->size : ''));
                @endphp
                <tr>
                    <td>
                        <div class="item-name">{{ $itemName }}</div>
                        <div class="item-meta">{{ $item->quantity }} бр. x {{ number_format($item->price, 2) }} €</div>
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
        <tr class="total-row">
            <td>Общо</td>
            <td style="text-align: right;">{{ number_format($order->total, 2) }} €<br><span class="muted">{{ number_format($order->total * 1.9558, 2) }} лв.</span></td>
        </tr>
    </table>

    <p style="margin: 0; color: #555555; font-size: 14px; line-height: 1.7;">
        Ще се свържем с теб с инструкции за връщането.
    </p>
@endsection

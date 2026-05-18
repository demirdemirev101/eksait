@extends('emails.layout')

@section('title', 'Нова поръчка')

@section('hero')
    <strong>Получена е нова поръчка #{{ $order->id }}</strong><br>
    Провери детайлите в административния панел.
@endsection

@section('content')
    <div class="section-title">Данни за поръчката</div>

    <table class="dark-box" role="presentation">
        <tr><td class="dark-label">Номер</td><td>#{{ $order->id }}</td></tr>
        <tr><td class="dark-label">Клиент</td><td>{{ $order->customer_name }}</td></tr>
        <tr><td class="dark-label">Email</td><td>{{ $order->customer_email }}</td></tr>
        <tr><td class="dark-label">Телефон</td><td>{{ $order->customer_phone ?: 'Няма' }}</td></tr>
        <tr><td class="dark-label">Град</td><td>{{ $order->shipping_city }}</td></tr>
        <tr><td class="dark-label">Сума</td><td>{{ number_format($order->subtotal, 2) }} € / {{ number_format($order->subtotal * 1.9558, 2) }} лв.</td></tr>
    </table>

    @if ($order->items->isNotEmpty())
        <div class="section-title">Артикули</div>
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
                        <td style="text-align: right; white-space: nowrap;">{{ number_format($item->total, 2) }} €</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <p style="margin: 0; color: #555555; font-size: 14px; line-height: 1.7;">
        Влез в административния панел за повече детайли и обработка на поръчката.
    </p>
@endsection

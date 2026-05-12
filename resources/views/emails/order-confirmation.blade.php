@extends('emails.layout')

@section('title', 'Потвърждение на поръчка')

@section('hero')
    <strong>Благодарим за поръчката, {{ $order->customer_name }}!</strong><br>
    Получихме твоята поръчка <strong>#{{ $order->id }}</strong> и ще я обработим възможно най-скоро.
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
                <tr>
                    <td>
                        <div class="item-name">{{ $item->product_name }}</div>
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
        <tr class="first-row">
            <td>Продукти</td>
            <td style="text-align: right;">
                {{ number_format($order->subtotal, 2) }} €<br>
                <span class="muted">{{ number_format($order->subtotal * 1.9558, 2) }} лв.</span>
            </td>
        </tr>
        <tr>
            <td>Доставка</td>
            <td style="text-align: right;">
                {{ number_format($order->shipping_price, 2) }} €<br>
                <span class="muted">{{ number_format($order->shipping_price * 1.9558, 2) }} лв.</span>
            </td>
        </tr>
        <tr class="total-row">
            <td>Общо</td>
            <td style="text-align: right;">
                {{ number_format($order->total, 2) }} €<br>
                <span class="muted">{{ number_format($order->total * 1.9558, 2) }} лв.</span>
            </td>
        </tr>
    </table>

    @if ($order->payment_method === 'bank_transfer')
        <div class="section-title">Банков превод</div>
        <table class="dark-box" role="presentation">
            <tr><td colspan="2" class="dark-title">Данни за плащане</td></tr>
            <tr><td class="dark-label">Получател</td><td>{{ config('services.bank_transfer.company_name') }}</td></tr>
            <tr><td class="dark-label">IBAN</td><td>{{ config('services.bank_transfer.iban') }}</td></tr>
            <tr><td class="dark-label">Банка</td><td>{{ config('services.bank_transfer.bank_name') }}</td></tr>
            <tr><td class="dark-label">BIC</td><td>{{ config('services.bank_transfer.bic') }}</td></tr>
            <tr><td class="dark-label">Сума</td><td>{{ number_format($order->total, 2) }} {{ config('services.bank_transfer.currency') }} / {{ number_format($order->total * 1.9558, 2) }} BGN</td></tr>
            <tr><td class="dark-label">Основание</td><td>Поръчка #{{ $order->id }}</td></tr>
            <tr><td colspan="2" class="dark-note">След като потвърдим получаването на плащането, ще подготвим и изпратим пратката ви.</td></tr>
        </table>
    @endif

    <div class="section-title">Адрес за доставка</div>
    <div class="info-box">
        <strong>{{ $order->customer_name }}</strong><br>
        {{ $order->shipping_address }}<br>
        {{ $order->shipping_city }}
    </div>

    <p style="margin: 0; color: #555555; font-size: 14px; line-height: 1.7;">
        При въпроси или нужда от съдействие, не се колебайте да се свържете с нас.<br>
        Ще се радваме да помогнем.
    </p>
@endsection

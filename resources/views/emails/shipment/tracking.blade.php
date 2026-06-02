@extends('emails.layout')

@php
    $isReturnShipment = $isReturnShipment ?? (($shipment?->direction ?? 'outbound') === 'return');
@endphp

@section('title', $isReturnShipment ? 'Инструкции за връщане' : 'Пратката е изпратена')

@section('hero')
    <strong>
        {{ $isReturnShipment ? 'Обратната ви пратка е създадена в Econt.' : 'Пратката ви е създадена в Econt.' }}
    </strong><br>
    Номер на пратка: <strong>{{ $trackingNumber ?? 'N/A' }}</strong>
@endsection

@section('content')
    @php
        $trackingUrl = null;
        if (! empty(config('services.econt.track_url')) && ! empty($trackingNumber)) {
            $trackingUrl = rtrim(config('services.econt.track_url'), '/');
        }
    @endphp

    <div class="section-title">{{ $isReturnShipment ? 'Връщане' : 'Проследяване' }}</div>

    <div class="info-box">
        <strong>Номер на пратка:</strong> {{ $trackingNumber ?? 'N/A' }}<br>
        @if ($shipment?->order)
            <strong>Поръчка:</strong> #{{ $shipment->order->id }}
        @endif
    </div>

    @if (! empty($labelUrl))
        <p style="margin: 0 0 12px;">
            <a href="{{ $labelUrl }}" class="button">{{ $isReturnShipment ? 'Отвори етикета за връщане' : 'Отвори етикета' }}</a>
        </p>
    @endif

    @if (! empty($trackingUrl))
        <p style="margin: 0 0 28px;">
            <a href="{{ $trackingUrl }}" class="button">{{ $isReturnShipment ? 'Проследи връщането' : 'Проследи пратката' }}</a>
        </p>
    @endif

    <p style="margin: 0; color: #555555; font-size: 14px; line-height: 1.7;">
        {{ $isReturnShipment ? 'Подготвихме обратната пратка и можеш да използваш номера за проследяване при връщането.' : 'Благодарим, че избрахте Excite Company.' }}
    </p>
@endsection

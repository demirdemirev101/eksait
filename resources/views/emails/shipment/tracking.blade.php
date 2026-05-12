@extends('emails.layout')

@section('title', 'Пратката е изпратена')

@section('hero')
    <strong>Пратката ви е създадена в Еконт.</strong><br>
    Номер на пратка: <strong>{{ $trackingNumber ?? 'N/A' }}</strong>
@endsection

@section('content')
    @php
        $trackingUrl = null;
        if (! empty(config('services.econt.track_url')) && ! empty($trackingNumber)) {
            $trackingUrl = rtrim(config('services.econt.track_url'), '/');
        }
    @endphp

    <div class="section-title">Проследяване</div>

    <div class="info-box">
        <strong>Номер на пратка:</strong> {{ $trackingNumber ?? 'N/A' }}<br>
        @if ($shipment?->order)
            <strong>Поръчка:</strong> #{{ $shipment->order->id }}
        @endif
    </div>

    @if (! empty($trackingUrl))
        <p style="margin: 0 0 28px;">
            <a href="{{ $trackingUrl }}" class="button">Проследи пратката</a>
        </p>
    @endif

    <p style="margin: 0; color: #555555; font-size: 14px; line-height: 1.7;">
        Благодарим, че избрахте Excite Company.
    </p>
@endsection

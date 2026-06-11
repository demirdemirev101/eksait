@extends('emails.layout')

@php
    $isReturnShipment = $isReturnShipment ?? (($shipment?->direction ?? 'outbound') === 'return');
    $titleKey = $isReturnShipment ? 'orders.mail.tracking.title_return' : 'orders.mail.tracking.title_outbound';
    $heroKey = $isReturnShipment ? 'orders.mail.tracking.hero_return' : 'orders.mail.tracking.hero_outbound';
    $sectionKey = $isReturnShipment ? 'orders.mail.tracking.section_return' : 'orders.mail.tracking.section_outbound';
    $labelKey = $isReturnShipment ? 'orders.mail.tracking.open_return_label' : 'orders.mail.tracking.open_label';
    $trackingKey = $isReturnShipment ? 'orders.mail.tracking.track_return' : 'orders.mail.tracking.track_shipment';
    $noteKey = $isReturnShipment ? 'orders.mail.tracking.note_return' : 'orders.mail.tracking.note_outbound';
@endphp

@section('title', __($titleKey))

@section('hero')
    <strong>{{ __($heroKey) }}</strong><br>
    {{ __('orders.mail.tracking.tracking_number') }}: <strong>{{ $trackingNumber ?? 'N/A' }}</strong>
@endsection

@section('content')
    @php
        $trackingUrl = null;
        if (! empty(config('services.econt.track_url')) && ! empty($trackingNumber)) {
            $trackingUrl = rtrim(config('services.econt.track_url'), '/');
        }
    @endphp

    <div class="section-title">{{ __($sectionKey) }}</div>

    <div class="info-box">
        <strong>{{ __('orders.mail.tracking.tracking_number') }}:</strong> {{ $trackingNumber ?? 'N/A' }}<br>
        @if ($shipment?->order)
            <strong>{{ __('orders.mail.tracking.order') }}:</strong> #{{ $shipment->order->id }}
        @endif
    </div>

    @if (! empty($labelUrl))
        <p style="margin: 0 0 12px;">
            <a href="{{ $labelUrl }}" class="button">{{ __($labelKey) }}</a>
        </p>
    @endif

    @if (! empty($trackingUrl))
        <p style="margin: 0 0 28px;">
            <a href="{{ $trackingUrl }}" class="button">{{ __($trackingKey) }}</a>
        </p>
    @endif

    <p style="margin: 0; color: #555555; font-size: 14px; line-height: 1.7;">
        {{ __($noteKey) }}
    </p>
@endsection

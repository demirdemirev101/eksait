<?php

namespace App\Mail;

use App\Models\Shipment;
use App\Support\LocalizedContent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ShipmentTrackingMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public int $shipmentId) {}

    public function build()
    {
        $shipment = Shipment::with('order')->find($this->shipmentId);
        $isReturnShipment = ($shipment?->direction ?? 'outbound') === 'return';
        $locale = LocalizedContent::normalizeLocale($shipment?->order?->locale ?? null);
        $subjectKey = $isReturnShipment
            ? 'orders.mail.tracking.subject_return'
            : 'orders.mail.tracking.subject_outbound';

        return $this
            ->locale($locale)
            ->subject(trans($subjectKey, ['order' => $shipment?->order?->id], $locale))
            ->view('emails.shipment.tracking', [
                'shipment' => $shipment,
                'trackingNumber' => $shipment?->tracking_number,
                'labelUrl' => $shipment?->label_url,
                'isReturnShipment' => $isReturnShipment,
            ]);
    }
}

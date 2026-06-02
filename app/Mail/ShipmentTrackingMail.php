<?php

namespace App\Mail;

use App\Models\Shipment;
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

        return $this
            ->subject($isReturnShipment
                ? 'Инструкции за връщане на поръчка #' . ($shipment?->order?->id ?? '')
                : 'Вашата поръчка е изпратена')
            ->view('emails.shipment.tracking', [
                'shipment' => $shipment,
                'trackingNumber' => $shipment?->tracking_number,
                'labelUrl' => $shipment?->label_url,
                'isReturnShipment' => $isReturnShipment,
            ]);
    }
}

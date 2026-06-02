<?php

namespace App\Listeners;

use App\Events\ShipmentCreated;
use App\Jobs\NotifyAdminShipmentFailedJob;
use App\Jobs\SendTrackingEmailJob;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\EcontShippingService;
use App\Support\ErrorMessages;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SendShipmentToEcont implements ShouldQueue
{
    use InteractsWithQueue;

    public $tries = 3;
    public $timeout = 60;
    public $backoff = [30, 60, 120];

    public function __construct(
        private EcontShippingService $econtShippingService,
    ) {}

    public function handle(ShipmentCreated $event): void
    {
        $shipment = Shipment::with('order')->findOrFail($event->shipmentId);
        $order = $shipment->order;

        if (! $order || $shipment->status !== 'created') {
            Log::warning('Shipment not ready for Econt', [
                'order_id' => $event->orderId,
                'shipment_id' => $shipment->id,
                'status' => $shipment->status,
            ]);

            return;
        }

        if (! config('services.econt.enabled')) {
            $shipment->update([
                'status' => 'confirmed',
                'tracking_number' => 'TEST-' . $shipment->id,
                'carrier_response' => [
                    'message' => 'Econt disabled (local environment)',
                ],
                'error_message' => null,
            ]);

            Log::info('Econt skipped (disabled)', [
                'shipment_id' => $shipment->id,
            ]);

            dispatch(new SendTrackingEmailJob($shipment->id));

            return;
        }

        $updated = Shipment::where('id', $shipment->id)
            ->where('status', 'created')
            ->update(['status' => 'pending']);

        if (! $updated) {
            Log::info('Shipment already being processed', [
                'shipment_id' => $shipment->id,
            ]);

            return;
        }

        $shipment->refresh();

        try {
            Log::info('Sending shipment to Econt', [
                'shipment_id' => $shipment->id,
            ]);

            $response = $this->econtShippingService->send($shipment);

            Log::info('Econt response received', [
                'shipment_id' => $shipment->id,
                'response' => $response,
            ]);

            $this->processResponse($shipment, $response);
        } catch (RuntimeException $e) {
            $this->handleError($shipment, $e);

            throw $e;
        } catch (\Exception $e) {
            $this->handleError($shipment, $e);

            throw $e;
        }
    }

    private function processResponse(Shipment $shipment, array $response): void
    {
        $label = $response['label'] ?? null;

        if (! $label || empty($label['shipmentNumber'])) {
            throw new RuntimeException('Invalid response from Econt: missing shipmentNumber');
        }

        $shipment->update([
            'carrier_response' => $response,
            'carrier_shipment_id' => $label['shipmentNumber'],
            'tracking_number' => $label['shipmentNumber'],
            'label_url' => $label['pdfURL'] ?? null,
            'shipping_price_real' => $label['totalPrice'] ?? null,
            'status' => 'confirmed',
            'sent_to_carrier_at' => now(),
            'error_message' => null,
        ]);

        Log::info('Shipment confirmed by Econt', [
            'shipment_id' => $shipment->id,
            'tracking_number' => $label['shipmentNumber'],
            'label_url' => $label['pdfURL'] ?? null,
        ]);

        dispatch(new SendTrackingEmailJob($shipment->id));
    }

    private function handleError(Shipment $shipment, \Exception $e): void
    {
        $errorMessage = $e->getMessage();

        Log::error('Econt shipment creation failed', [
            'shipment_id' => $shipment->id,
            'error' => $errorMessage,
            'attempt' => $this->attempts(),
        ]);

        $status = $this->attempts() >= $this->tries ? 'error' : 'pending';

        $shipment->update([
            'status' => $status,
            'error_message' => ErrorMessages::SHIPMENT_CREATE_FAILED . ' ' . $errorMessage,
        ]);
    }

    public function failed($event, \Throwable $exception): void
    {
        $order = Order::with('shipment')->find($event->orderId);

        if ($order && $order->shipment) {
            $order->shipment->update([
                'status' => 'error',
                'error_message' => ErrorMessages::SHIPMENT_CREATE_FAILED_AFTER_RETRIES . ' ' . $exception->getMessage(),
            ]);

            Log::critical('Econt shipment job failed permanently', [
                'order_id' => $order->id,
                'shipment_id' => $order->shipment->id,
                'error' => $exception->getMessage(),
            ]);

            dispatch(new NotifyAdminShipmentFailedJob($order->shipment->id));
        }
    }
}

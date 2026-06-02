<?php

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Models\Order;

class ShipmentPollingPolicy
{
    private const TERMINAL_SHIPMENT_STATUSES = [
        'delivered',
        'cancelled',
        'returned',
    ];

    /**
     * Determines if shipment polling should stop based on the order status. If the order is completed, cancelled, or return requested/returned,
     *  there is no need to continue polling for shipment updates.
     */
    private function shouldStopShipmentPolling(Order $record): bool
    {
        if ($record->status === OrderStatus::RETURN_REQUESTED->value) {
            $record->loadMissing('returnShipment');

            return ! $record->returnShipment
                || in_array($record->returnShipment->status, self::TERMINAL_SHIPMENT_STATUSES, true);
        }

        return in_array($record->status, [
            OrderStatus::COMPLETED->value,
            OrderStatus::CANCELLED->value,
            OrderStatus::RETURNED->value,
        ], true);
    }
    
    /**
     * Determines if shipment polling should continue for the current order. Polling should continue
     *  if the order has a shipment with a carrier shipment ID and is not in a status that indicates it should stop.
     */
    public function shouldPollShipmentStatus(Order $record): bool
    {
        if (! $record) {
            return false;
        }

        $record->loadMissing(['shipment', 'returnShipment']);

        if ($record->status === OrderStatus::RETURN_REQUESTED->value) {
            return $record->returnShipment !== null
                && ! in_array($record->returnShipment->status, self::TERMINAL_SHIPMENT_STATUSES, true);
        }

        if ($this->shouldStopShipmentPolling($record)) {
            return false;
        }

        return $record->shipment !== null
            && ! in_array($record->shipment->status, self::TERMINAL_SHIPMENT_STATUSES, true);
    }

}

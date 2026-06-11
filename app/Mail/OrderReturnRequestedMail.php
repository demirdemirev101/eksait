<?php

namespace App\Mail;

use App\Models\Order;
use App\Support\LocalizedContent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderReturnRequestedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(public int $orderId)
    {
        $this->order = Order::with(['items.product', 'items.variant'])->findOrFail($orderId);
    }

    public function build()
    {
        $locale = LocalizedContent::normalizeLocale($this->order->locale ?? null);

        return $this
            ->locale($locale)
            ->subject(trans('orders.mail.return_requested.subject', ['order' => $this->order->id], $locale))
            ->view('emails.order-return-requested')
            ->with([
                'order' => $this->order,
            ]);
    }
}

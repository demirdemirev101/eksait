<?php

namespace App\Mail;

use App\Models\Order;
use App\Support\LocalizedContent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
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
            ->subject(trans('orders.mail.confirmation.subject', ['order' => $this->order->id], $locale))
            ->view('emails.order-confirmation')
            ->with([
                'order' => $this->order,
            ]);
    }
}

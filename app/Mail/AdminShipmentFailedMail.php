<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminShipmentFailedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Грешка при пратка',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.shipment.failed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

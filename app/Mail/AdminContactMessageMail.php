<?php

namespace App\Mail;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminContactMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Contact $contact) {}

    public function build()
    {
        return $this->subject('Ново съобщение от контактната форма')
            ->view('emails.admin_contact_message')
            ->with([
                'name' => $this->contact->name,
                'email' => $this->contact->email,
                'phone' => $this->contact->phone ?? 'Няма',
                'messageContent' => $this->contact->message,
            ]);
    }
}

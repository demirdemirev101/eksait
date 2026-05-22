<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Възстановяване на парола')
            ->view('emails.auth.reset-password', [
                'expiresInMinutes' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire'),
                'resetUrl' => $this->resetUrl($notifiable),
                'user' => $notifiable,
            ]);
    }
}

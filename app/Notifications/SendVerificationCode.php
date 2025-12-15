<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendVerificationCode extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $code)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Verification Code')
            ->greeting('Hi ' . ($notifiable->name ?? ''))
            ->line('Use this code to verify your account:')
            ->line("**{$this->code}**")
            ->line('This code expires in 15 minutes.')
            ->line('If you did not request this, you can ignore this email.');
    }
}

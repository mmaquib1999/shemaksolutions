<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionPaymentMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $planName;
    public string $amount;

    /**
     * Create a new message instance.
     */
    public function __construct(string $planName, string $amount)
    {
        $this->planName = $planName;
        $this->amount = $amount;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->subject('Payment received for your subscription')
            ->view('emails.subscription-paid')
            ->with([
                'plan' => $this->planName,
                'amount' => $this->amount,
            ]);
    }
}

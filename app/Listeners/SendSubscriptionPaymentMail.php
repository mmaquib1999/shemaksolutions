<?php

namespace App\Listeners;

use App\Mail\SubscriptionPaymentMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Events\WebhookReceived;

class SendSubscriptionPaymentMail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(WebhookReceived $event): void
    {
        $payload = $event->payload;
        $type = $payload['type'] ?? '';
        $object = $payload['data']['object'] ?? [];

        // checkout.session.completed (we set client_reference_id to user id)
        if ($type === 'checkout.session.completed') {
            $userId = $object['client_reference_id'] ?? null;
            if (! $userId) {
                return;
            }
            $user = User::find($userId);
            if (! $user) {
                return;
            }

            $planName = $object['metadata']['plan_name'] ?? 'Subscription';
            $amount = $this->formatAmount($object['amount_total'] ?? 0, $object['currency'] ?? 'usd');

            Mail::to($user->email)->queue(new SubscriptionPaymentMail($planName, $amount));
            return;
        }

        // invoice.payment_succeeded (recurring)
        if ($type === 'invoice.payment_succeeded') {
            $customerId = $object['customer'] ?? null;
            if (! $customerId) {
                return;
            }

            $user = User::where('stripe_id', $customerId)->first();
            if (! $user) {
                return;
            }

            $planName = $this->invoicePlanName($object);
            $amount = $this->formatAmount($object['amount_paid'] ?? 0, $object['currency'] ?? 'usd');

            Mail::to($user->email)->queue(new SubscriptionPaymentMail($planName, $amount));
        }
    }

    private function invoicePlanName(array $invoice): string
    {
        $lines = $invoice['lines']['data'] ?? [];
        if (! empty($lines)) {
            $description = $lines[0]['description'] ?? null;
            if ($description) {
                return $description;
            }
        }
        return 'Subscription';
    }

    private function formatAmount(int $amount, string $currency): string
    {
        if ($amount <= 0) {
            return strtoupper($currency) . ' 0.00';
        }
        return strtoupper($currency) . ' ' . number_format($amount / 100, 2);
    }
}

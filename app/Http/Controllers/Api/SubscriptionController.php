<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiProviderKey;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Subscription;

class SubscriptionController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $subscription = $user->subscription('default');

        $queriesUsed = (int) AiProviderKey::where('user_id', $user->id)->sum('total_queries');
        $plan = $this->planFor($subscription, null, $queriesUsed);

        $stripeSubscription = null;
        if ($subscription && $this->stripeConfigured()) {
            try {
                $stripeSubscription = $subscription->asStripeSubscription();
            } catch (\Throwable $e) {
                // Ignore Stripe fetch issues and fall back to database values
            }
        }

        $nextBillingDate = $stripeSubscription?->current_period_end
            ? Carbon::createFromTimestamp($stripeSubscription->current_period_end)->toDateString()
            : null;

        $payload = [
            'subscription' => $this->serializeSubscription($subscription, $plan, $nextBillingDate),
            'customer' => [
                'email' => $user->email,
                'pm_last_four' => $user->pm_last_four,
                'pm_type' => $user->pm_type,
                'stripe_id' => $user->stripe_id,
            ],
            'usage' => $this->usage($user, $plan['query_limit'], $queriesUsed),
            'invoices' => $this->invoicePayloads($user),
            'plans' => $this->availablePlans(),
            'recommended_plan' => $plan['name'] ?? null,
        ];

        return response()->json($payload);
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'price_id' => ['nullable', 'string'],
            'success_url' => ['nullable', 'url'],
            'cancel_url' => ['nullable', 'url'],
        ]);

        $user = $request->user();
        $queriesUsed = (int) AiProviderKey::where('user_id', $user->id)->sum('total_queries');

        $plan = $this->planFor(null, $validated['price_id'] ?? null, $queriesUsed);

        if (empty($plan['price_id'])) {
            return response()->json(['message' => 'Stripe price id missing for the selected plan.'], 422);
        }

        if (! $this->withinPlanLimit($plan, $queriesUsed)) {
            return response()->json(['message' => 'Your usage exceeds this plan. Please choose a higher plan.'], 422);
        }

        $customerId = $user->createOrGetStripeCustomer();

        $successUrl = $validated['success_url'] ?? $this->defaultSuccessUrl();
        $cancelUrl = $validated['cancel_url'] ?? $this->defaultCancelUrl();

        try {
            $session = Cashier::stripe()->checkout->sessions->create([
                'mode' => 'subscription',
                'customer' => $customerId,
                'client_reference_id' => (string) $user->getKey(),
                'metadata' => [
                    'plan_name' => $plan['name'] ?? 'Subscription',
                ],
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $plan['price_id'],
                    'quantity' => 1,
                ]],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Unable to start checkout with Stripe.',
                'error' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'id' => $session->id,
            'url' => $session->url,
        ]);
    }

    public function portal(Request $request)
    {
        $user = $request->user();
        $returnUrl = $request->input('return_url', config('subscriptions.billing_portal_return_url'));

        $user->createOrGetStripeCustomer();

        try {
            $url = $user->billingPortalUrl($returnUrl);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Unable to open Stripe billing portal.',
                'error' => $e->getMessage(),
            ], 422);
        }

        return response()->json(['url' => $url]);
    }

    public function cancel(Request $request)
    {
        $user = $request->user();
        $subscription = $user->subscription('default');

        if (! $subscription) {
            return response()->json(['message' => 'No active subscription found.'], 404);
        }

        if ($subscription->ended() && ! $subscription->onGracePeriod()) {
            return response()->json(['message' => 'Subscription already ended.'], 409);
        }

        $subscription->cancel();

        return response()->json([
            'message' => 'Subscription cancelled. Access remains until the end of the billing period.',
            'subscription' => $this->serializeSubscription($subscription->fresh()),
        ]);
    }

    public function resume(Request $request)
    {
        $user = $request->user();
        $subscription = $user->subscription('default');

        if (! $subscription) {
            return response()->json(['message' => 'No subscription to resume.'], 404);
        }

        if (! $subscription->onGracePeriod()) {
            return response()->json(['message' => 'The subscription is not within its grace period.'], 409);
        }

        $subscription->resume();

        return response()->json([
            'message' => 'Subscription resumed.',
            'subscription' => $this->serializeSubscription($subscription->fresh()),
        ]);
    }

    public function invoices(Request $request)
    {
        return response()->json([
            'invoices' => $this->invoicePayloads($request->user()),
        ]);
    }

    public function exportInvoices(Request $request)
    {
        $invoices = $this->invoicePayloads($request->user());

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="invoices.csv"',
        ];

        $callback = static function () use ($invoices) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'date', 'amount', 'currency', 'status', 'hosted_invoice_url', 'invoice_pdf']);
            foreach ($invoices as $inv) {
                fputcsv($out, [
                    $inv['id'] ?? '',
                    $inv['date'] ?? '',
                    $inv['amount'] ?? '',
                    $inv['currency'] ?? '',
                    $inv['status'] ?? '',
                    $inv['hosted_invoice_url'] ?? '',
                    $inv['invoice_pdf'] ?? '',
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function availablePlans(): array
    {
        return collect(config('subscriptions.plans'))
            ->map(function ($plan) {
                return [
                    'name' => $plan['name'],
                    'price_id' => $plan['price_id'],
                    'amount' => $plan['amount'],
                    'amount_formatted' => $this->formatAmount($plan['amount']),
                    'interval' => $plan['interval'],
                    'query_limit' => $plan['query_limit'],
                ];
            })
            ->values()
            ->all();
    }

    private function planFor(?Subscription $subscription, ?string $priceId = null, int $queriesUsed = 0): array
    {
        $plans = collect(config('subscriptions.plans', []));

        $default = [
            'name' => 'Professional',
            'price_id' => $priceId,
            'amount' => 9900,
            'interval' => 'month',
            'query_limit' => 10000,
        ];

        if ($plans->isEmpty()) {
            return $default;
        }

        $plan = null;

        if ($priceId) {
            $plan = $plans->first(fn ($p) => ($p['price_id'] ?? null) === $priceId);
        }

        if (! $plan && $subscription) {
            $plan = $plans->first(fn ($p) => ($p['price_id'] ?? null) === $subscription->stripe_price);
        }

        if (! $plan) {
            // Usage-based defaults
            $starter = $plans->firstWhere('name', 'Starter');
            $pro = $plans->firstWhere('name', 'Professional');
            $enterprise = $plans->firstWhere('name', 'Enterprise') ?? $plans->last();

            if ($starter && $this->withinPlanLimit($starter, $queriesUsed)) {
                $plan = $starter;
            } elseif ($pro && $this->withinPlanLimit($pro, $queriesUsed)) {
                $plan = $pro;
            } else {
                $plan = $enterprise;
            }
        }

        return $plan ?? $default;
    }

    private function usage($user, ?int $limit, ?int $alreadyComputed = null): array
    {
        $queriesUsed = $alreadyComputed ?? (int) AiProviderKey::where('user_id', $user->id)->sum('total_queries');

        return [
            'queries_used' => $queriesUsed,
            'queries_limit' => $limit,
        ];
    }

    private function invoicePayloads($user): array
    {
        if (! $this->stripeConfigured() || ! $user->stripe_id) {
            return [];
        }

        try {
            return collect($user->invoicesIncludingPending())
                ->map(function ($invoice) {
                    $stripeInvoice = $invoice->asStripeInvoice();

                    return [
                        'id' => $invoice->id,
                        'number' => $invoice->number(),
                        'date' => $invoice->date()->toDateString(),
                        'amount' => $invoice->total(),
                        'currency' => strtoupper($stripeInvoice->currency),
                        'status' => $stripeInvoice->status,
                        'hosted_invoice_url' => $stripeInvoice->hosted_invoice_url,
                        'invoice_pdf' => $stripeInvoice->invoice_pdf,
                    ];
                })
                ->values()
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function serializeSubscription(?Subscription $subscription, ?array $plan = null, ?string $renewsAt = null): array
    {
        $plan = $plan ?? $this->planFor($subscription);
        $isStarterDefault = ! $subscription && ($plan['name'] ?? '') === 'Starter';

        return [
            'name' => $plan['name'],
            'price_id' => $plan['price_id'],
            'amount' => $plan['amount'],
            'amount_formatted' => $this->formatAmount($plan['amount']),
            'interval' => $plan['interval'],
            'status' => $subscription?->stripe_status ?? ($isStarterDefault ? 'active' : 'inactive'),
            'renews_at' => $renewsAt,
            'cancel_at' => $subscription?->ends_at?->toDateString(),
            'on_grace_period' => $subscription?->onGracePeriod() ?? false,
            'is_active' => $subscription?->valid() ?? $isStarterDefault,
        ];
    }

    private function formatAmount(int $amount): string
    {
        $currency = strtoupper(config('subscriptions.currency', 'USD'));
        $symbol = $currency === 'USD' ? '$' : $currency . ' ';
        $value = number_format(max($amount, 0) / 100, 2);

        return $symbol . $value;
    }

    private function stripeConfigured(): bool
    {
        return (bool) config('services.stripe.secret');
    }

    private function withinPlanLimit(array $plan, int $queriesUsed): bool
    {
        if (! isset($plan['query_limit']) || $plan['query_limit'] === null) {
            return true;
        }

        return $queriesUsed <= (int) $plan['query_limit'];
    }

    private function defaultSuccessUrl(): string
    {
        return url('/settings?checkout=success&session_id={CHECKOUT_SESSION_ID}');
    }

    private function defaultCancelUrl(): string
    {
        return url('/settings?checkout=cancelled');
    }
}

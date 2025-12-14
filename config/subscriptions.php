<?php

return [
    'currency' => env('CASHIER_CURRENCY', 'usd'),
    'plans' => [
        'starter' => [
            'name' => 'Starter',
            'price_id' => env('STRIPE_PRICE_ID_STARTER'),
            'amount' => 0,
            'interval' => 'month',
            'query_limit' => 100,
        ],
        'pro' => [
            'name' => 'Professional',
            'price_id' => env('STRIPE_PRICE_ID_PRO', env('STRIPE_PRICE_ID')),
            'amount' => 9900,
            'interval' => 'month',
            'query_limit' => 10000,
        ],
        'enterprise' => [
            'name' => 'Enterprise',
            'price_id' => env('STRIPE_PRICE_ID_ENTERPRISE'),
            'amount' => 29900,
            'interval' => 'month',
            'query_limit' => null,
        ],
    ],
    'default_plan' => env('STRIPE_DEFAULT_PLAN', 'starter'),
    'trial_days' => env('STRIPE_TRIAL_DAYS', 0),
    'billing_portal_return_url' => env('STRIPE_BILLING_RETURN_URL', env('APP_URL') . '/settings'),
];

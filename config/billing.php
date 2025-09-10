<?php

/**
 * Billing & Subscription Configuration
 * 
 * Configuration for subscription management, pricing, and billing
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Billing Settings
    |--------------------------------------------------------------------------
    |
    | Core billing configuration including test mode and callback URLs
    |
    */
    'test_mode' => env('SHOPIFY_BILLING_TEST', true),
    
    'callback_url' => env('SHOPIFY_BILLING_CALLBACK_URL', env('APP_URL', 'http://localhost:8000') . '/billing/callback'),
    
    'webhook_url' => env('SHOPIFY_BILLING_WEBHOOK_URL', env('APP_URL', 'http://localhost:8000') . '/webhooks/billing'),

    /*
    |--------------------------------------------------------------------------
    | Subscription Pricing
    |--------------------------------------------------------------------------
    |
    | Default pricing for different subscription plans
    | These can be overridden in the main application config
    |
    */
    'pricing' => [
        'monthly' => [
            'price' => env('SUBSCRIPTION_MONTHLY_PRICE', 29.99),
            'interval' => 'EVERY_30_DAYS',
            'name' => 'Monthly Plan',
            'trial_days' => env('SUBSCRIPTION_MONTHLY_TRIAL_DAYS', 14),
        ],
        
        'yearly' => [
            'price' => env('SUBSCRIPTION_YEARLY_PRICE', 299.99),
            'interval' => 'ANNUAL',
            'name' => 'Yearly Plan',
            'trial_days' => env('SUBSCRIPTION_YEARLY_TRIAL_DAYS', 30),
        ],
        
        'lifetime' => [
            'price' => env('SUBSCRIPTION_LIFETIME_PRICE', 999.99),
            'interval' => 'ANNUAL', // Closest to lifetime in Shopify
            'name' => 'Lifetime Plan',
            'trial_days' => env('SUBSCRIPTION_LIFETIME_TRIAL_DAYS', 7),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Trial Configurations
    |--------------------------------------------------------------------------
    |
    | Default trial periods for different plans
    |
    */
    'trial_days' => [
        'default' => env('SUBSCRIPTION_DEFAULT_TRIAL_DAYS', 14),
        'monthly' => env('SUBSCRIPTION_MONTHLY_TRIAL_DAYS', 14),
        'yearly' => env('SUBSCRIPTION_YEARLY_TRIAL_DAYS', 30),
        'lifetime' => env('SUBSCRIPTION_LIFETIME_TRIAL_DAYS', 7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Free Time Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for free time grants and extensions
    |
    */
    'free_time' => [
        'max_days_per_grant' => env('SUBSCRIPTION_MAX_FREE_DAYS', 365),
        'max_total_free_days' => env('SUBSCRIPTION_MAX_TOTAL_FREE_DAYS', 730),
        'welcome_bonus_days' => env('SUBSCRIPTION_WELCOME_BONUS_DAYS', 7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Limits
    |--------------------------------------------------------------------------
    |
    | Limits and constraints for subscription management
    |
    */
    'limits' => [
        'max_active_subscriptions_per_user' => 1,
        'cancellation_grace_period_hours' => 24,
        'reactivation_window_days' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhooks Configuration
    |--------------------------------------------------------------------------
    |
    | Webhook topics to automatically register for billing events
    |
    */
    'webhooks' => [
        'app_subscriptions/update' => [
            'callback' => '/webhooks/app_subscriptions/update',
            'format' => 'JSON',
        ],
        'app_purchases_one_time/update' => [
            'callback' => '/webhooks/app_purchases_one_time/update', 
            'format' => 'JSON',
        ],
        'app_uninstalled' => [
            'callback' => '/webhooks/app_uninstalled',
            'format' => 'JSON',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Billing Features
    |--------------------------------------------------------------------------
    |
    | Feature flags for different billing capabilities
    |
    */
    'features' => [
        'usage_charges' => env('BILLING_ENABLE_USAGE_CHARGES', false),
        'one_time_charges' => env('BILLING_ENABLE_ONE_TIME_CHARGES', true),
        'recurring_charges' => env('BILLING_ENABLE_RECURRING_CHARGES', true),
        'coupons' => env('BILLING_ENABLE_COUPONS', true),
        'free_time_grants' => env('BILLING_ENABLE_FREE_TIME_GRANTS', true),
        'automatic_billing' => env('BILLING_ENABLE_AUTOMATIC', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency Settings
    |--------------------------------------------------------------------------
    |
    | Default currency and formatting options
    |
    */
    'currency' => [
        'default' => env('BILLING_CURRENCY', 'USD'),
        'symbol' => env('BILLING_CURRENCY_SYMBOL', '$'),
        'decimal_places' => env('BILLING_DECIMAL_PLACES', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Settings for billing-related notifications
    |
    */
    'notifications' => [
        'admin_email' => env('BILLING_ADMIN_EMAIL', env('ADMIN_EMAIL')),
        'send_cancellation_notifications' => env('BILLING_SEND_CANCELLATION_NOTIFICATIONS', true),
        'send_upgrade_notifications' => env('BILLING_SEND_UPGRADE_NOTIFICATIONS', true),
        'send_payment_failure_notifications' => env('BILLING_SEND_PAYMENT_FAILURE_NOTIFICATIONS', true),
    ],
];
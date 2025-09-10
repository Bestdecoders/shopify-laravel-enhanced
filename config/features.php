<?php

/**
 * Feature Configuration
 * 
 * Feature flags and settings for different package capabilities
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Core Features
    |--------------------------------------------------------------------------
    |
    | Core package feature toggles
    |
    */
    'enabled' => [
        'subscription_management' => env('FEATURE_SUBSCRIPTION_MANAGEMENT', true),
        'coupon_system' => env('FEATURE_COUPON_SYSTEM', true),
        'webhook_handlers' => env('FEATURE_WEBHOOK_HANDLERS', true),
        'graphql_service' => env('FEATURE_GRAPHQL_SERVICE', true),
        'admin_interface' => env('FEATURE_ADMIN_INTERFACE', true),
        'test_endpoints' => env('FEATURE_TEST_ENDPOINTS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Features
    |--------------------------------------------------------------------------
    |
    | Subscription-related feature flags
    |
    */
    'subscriptions' => [
        'recurring_billing' => env('FEATURE_RECURRING_BILLING', true),
        'usage_billing' => env('FEATURE_USAGE_BILLING', false),
        'one_time_purchases' => env('FEATURE_ONE_TIME_PURCHASES', true),
        'trial_periods' => env('FEATURE_TRIAL_PERIODS', true),
        'free_time_grants' => env('FEATURE_FREE_TIME_GRANTS', true),
        'subscription_cancellation' => env('FEATURE_SUBSCRIPTION_CANCELLATION', true),
        'subscription_reactivation' => env('FEATURE_SUBSCRIPTION_REACTIVATION', true),
        'plan_upgrades' => env('FEATURE_PLAN_UPGRADES', false),
        'plan_downgrades' => env('FEATURE_PLAN_DOWNGRADES', false),
        'proration' => env('FEATURE_PRORATION', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Coupon Features
    |--------------------------------------------------------------------------
    |
    | Coupon and discount-related features
    |
    */
    'coupons' => [
        'percentage_discounts' => env('FEATURE_PERCENTAGE_DISCOUNTS', true),
        'fixed_amount_discounts' => env('FEATURE_FIXED_AMOUNT_DISCOUNTS', true),
        'free_time_coupons' => env('FEATURE_FREE_TIME_COUPONS', true),
        'usage_limits' => env('FEATURE_COUPON_USAGE_LIMITS', true),
        'expiration_dates' => env('FEATURE_COUPON_EXPIRATION', true),
        'plan_restrictions' => env('FEATURE_COUPON_PLAN_RESTRICTIONS', true),
        'minimum_amount' => env('FEATURE_COUPON_MINIMUM_AMOUNT', true),
        'stackable_coupons' => env('FEATURE_STACKABLE_COUPONS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | GraphQL Features
    |--------------------------------------------------------------------------
    |
    | GraphQL service capabilities
    |
    */
    'graphql' => [
        'query_caching' => env('FEATURE_GRAPHQL_CACHING', false),
        'query_logging' => env('FEATURE_GRAPHQL_LOGGING', true),
        'error_handling' => env('FEATURE_GRAPHQL_ERROR_HANDLING', true),
        'retry_mechanism' => env('FEATURE_GRAPHQL_RETRY', true),
        'rate_limit_handling' => env('FEATURE_GRAPHQL_RATE_LIMIT_HANDLING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Features
    |--------------------------------------------------------------------------
    |
    | Webhook processing capabilities
    |
    */
    'webhooks' => [
        'signature_verification' => env('FEATURE_WEBHOOK_SIGNATURE_VERIFICATION', true),
        'automatic_registration' => env('FEATURE_WEBHOOK_AUTO_REGISTRATION', false),
        'retry_failed_webhooks' => env('FEATURE_WEBHOOK_RETRY', true),
        'webhook_logging' => env('FEATURE_WEBHOOK_LOGGING', true),
        'gdpr_webhooks' => env('FEATURE_GDPR_WEBHOOKS', true),
        'billing_webhooks' => env('FEATURE_BILLING_WEBHOOKS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Interface Features
    |--------------------------------------------------------------------------
    |
    | Admin panel and management interface features
    |
    */
    'admin' => [
        'user_management' => env('FEATURE_ADMIN_USER_MANAGEMENT', true),
        'subscription_overview' => env('FEATURE_ADMIN_SUBSCRIPTION_OVERVIEW', true),
        'coupon_management' => env('FEATURE_ADMIN_COUPON_MANAGEMENT', true),
        'analytics_dashboard' => env('FEATURE_ADMIN_ANALYTICS', false),
        'export_functionality' => env('FEATURE_ADMIN_EXPORT', false),
        'bulk_operations' => env('FEATURE_ADMIN_BULK_OPERATIONS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Testing Features
    |--------------------------------------------------------------------------
    |
    | Development and testing-related features
    |
    */
    'testing' => [
        'test_endpoints' => env('FEATURE_TEST_ENDPOINTS', app()->environment(['local', 'testing'])),
        'debug_mode' => env('FEATURE_DEBUG_MODE', app()->environment(['local', 'testing'])),
        'mock_shopify_api' => env('FEATURE_MOCK_SHOPIFY_API', false),
        'test_data_seeders' => env('FEATURE_TEST_DATA_SEEDERS', app()->environment(['local', 'testing'])),
        'performance_profiling' => env('FEATURE_PERFORMANCE_PROFILING', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Features
    |--------------------------------------------------------------------------
    |
    | Security-related feature flags
    |
    */
    'security' => [
        'csrf_protection' => env('FEATURE_CSRF_PROTECTION', true),
        'rate_limiting' => env('FEATURE_RATE_LIMITING', true),
        'request_logging' => env('FEATURE_REQUEST_LOGGING', true),
        'input_validation' => env('FEATURE_INPUT_VALIDATION', true),
        'api_key_rotation' => env('FEATURE_API_KEY_ROTATION', false),
        'audit_trail' => env('FEATURE_AUDIT_TRAIL', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Integration Features
    |--------------------------------------------------------------------------
    |
    | Third-party integration capabilities
    |
    */
    'integrations' => [
        'email_notifications' => env('FEATURE_EMAIL_NOTIFICATIONS', true),
        'slack_notifications' => env('FEATURE_SLACK_NOTIFICATIONS', false),
        'analytics_tracking' => env('FEATURE_ANALYTICS_TRACKING', false),
        'external_logging' => env('FEATURE_EXTERNAL_LOGGING', false),
        'payment_processors' => env('FEATURE_PAYMENT_PROCESSORS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Features
    |--------------------------------------------------------------------------
    |
    | Performance optimization features
    |
    */
    'performance' => [
        'query_optimization' => env('FEATURE_QUERY_OPTIMIZATION', true),
        'response_caching' => env('FEATURE_RESPONSE_CACHING', false),
        'background_processing' => env('FEATURE_BACKGROUND_PROCESSING', false),
        'database_indexing' => env('FEATURE_DATABASE_INDEXING', true),
        'lazy_loading' => env('FEATURE_LAZY_LOADING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | UI/UX Features
    |--------------------------------------------------------------------------
    |
    | User interface and experience features
    |
    */
    'ui' => [
        'dark_mode' => env('FEATURE_DARK_MODE', false),
        'responsive_design' => env('FEATURE_RESPONSIVE_DESIGN', true),
        'accessibility_features' => env('FEATURE_ACCESSIBILITY', true),
        'multi_language' => env('FEATURE_MULTI_LANGUAGE', false),
        'custom_themes' => env('FEATURE_CUSTOM_THEMES', false),
    ],
];
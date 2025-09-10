<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Core Package Configuration
    |--------------------------------------------------------------------------
    |
    | Basic package settings and references to other config files
    |
    */
    'admin_email' => env('ADMIN_EMAIL', 'admin1@example.com'),
    'thanks_email_template' => 'emails.thanks',
    'user_model' => \App\Models\User::class, // Default User model
    'auto_register_install_job' => true,

    /*
    |--------------------------------------------------------------------------
    | Configuration References
    |--------------------------------------------------------------------------
    |
    | References to other configuration files in the package
    | These files can be published and customized per project
    |
    */
    'graphql_queries_config' => 'shopify-enhanced-graphql-queries',
    'billing_config' => 'shopify-enhanced-billing', 
    'features_config' => 'shopify-enhanced-features',

    /*
    |--------------------------------------------------------------------------
    | Helper Methods for Config Access
    |--------------------------------------------------------------------------
    |
    | These methods provide easy access to configurations from other files
    |
    */
    'get_graphql_query' => function($category, $key) {
        $queries = config('shopify-enhanced-graphql-queries');
        return $queries[$category][$key] ?? null;
    },

    'get_billing_config' => function($key) {
        return config("shopify-enhanced-billing.{$key}");
    },

    'is_feature_enabled' => function($feature) {
        return config("shopify-enhanced-features.enabled.{$feature}", false);
    },

    // ===========================================
    // WEBHOOK CONFIGURATION
    // ===========================================
    
    'webhooks' => [
        // Webhook signature validation
        'webhook_secret' => env('SHOPIFY_WEBHOOK_SECRET', env('SHOPIFY_WEBHOOK_SECRET')),
        'validate_webhooks' => env('VALIDATE_SHOPIFY_WEBHOOKS', true),
        
        // Webhook handler service binding
        'handler_service' => env('WEBHOOK_HANDLER_SERVICE', \Bestdecoders\ShopifyLaravelEnhanced\Services\WebhookHandlerService::class),
        
        // Webhook logging
        'log_webhooks' => env('LOG_WEBHOOKS', true),
        'log_webhook_payloads' => env('LOG_WEBHOOK_PAYLOADS', false), // Set to true for debugging
        
        // Webhook rate limiting
        'rate_limit_enabled' => env('WEBHOOK_RATE_LIMIT', true),
        'rate_limit_max_attempts' => env('WEBHOOK_RATE_LIMIT_ATTEMPTS', 60),
        'rate_limit_decay_minutes' => env('WEBHOOK_RATE_LIMIT_DECAY', 1),
        
        // Mandatory webhooks (cannot be disabled)
        'mandatory_webhooks' => [
            'customers/data_request',
            'customers/redact',
            'shop/redact'
        ],
        
        // Optional business webhooks (can be enabled/disabled)
        'optional_webhooks' => [
            'orders/create' => env('WEBHOOK_ORDERS_CREATE', false),
            'products/update' => env('WEBHOOK_PRODUCTS_UPDATE', false),
            'custom/events' => env('WEBHOOK_CUSTOM_EVENTS', false),
        ],
        
        // Webhook URLs (automatically generated, but can be overridden)
        'base_url' => env('APP_URL', 'https://your-app.com'),
        'webhook_prefix' => 'webhooks',
        
        // GDPR compliance settings
        'gdpr' => [
            'data_retention_days' => env('GDPR_DATA_RETENTION_DAYS', 30),
            'auto_cleanup_enabled' => env('GDPR_AUTO_CLEANUP', true),
            'notification_email' => env('GDPR_NOTIFICATION_EMAIL', env('ADMIN_EMAIL')),
        ],
        
        // Webhook retry configuration
        'retry' => [
            'enabled' => env('WEBHOOK_RETRY_ENABLED', true),
            'max_attempts' => env('WEBHOOK_RETRY_ATTEMPTS', 3),
            'delay_seconds' => env('WEBHOOK_RETRY_DELAY', 60),
        ],
    ],
    'queries' => [
        'shop' => <<<GRAPHQL
            query {
                shop {
                    name
                    currencyCode
                    contactEmail
                    email
                    paymentSettings {
                        supportedDigitalWallets
                    }
                    plan {
                        displayName
                        partnerDevelopment
                        shopifyPlus
                    }
                }
            }
        GRAPHQL,
        'product' => [
            'search' => <<<GRAPHQL
                query (\$query: String, \$cursor: String, \$pageSize: Int!) {
                    products(first: \$pageSize, after: \$cursor, query: \$query) {
                        pageInfo {
                            hasNextPage
                            hasPreviousPage
                        }
                        edges {
                            node {
                                id
                                title
                                vendor
                                status
                                featuredImage {
                                    url
                                }
                                variants(first: 10) {
                                    edges {
                                        node {
                                            id
                                            title
                                            price
                                            availableForSale
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            GRAPHQL,

        ],
    ],

];

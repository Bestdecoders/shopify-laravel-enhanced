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

    // ===========================================
    // PRODUCT FILTER CONFIGURATION
    // ===========================================

    'product_filter' => [
        // Feature activation
        'enabled' => env('PRODUCT_FILTER_ENABLED', false),

        // Required scopes for product filtering
        'required_scopes' => ['read_products'],
        'optional_scopes' => ['read_inventory'], // For inventory data

        // Cache settings
        'cache_ttl_days' => env('PRODUCT_FILTER_CACHE_TTL', 30),
        'include_inventory_data' => env('PRODUCT_FILTER_INCLUDE_INVENTORY', false),

        // Webhook filtering
        'webhook_filtering_enabled' => env('PRODUCT_FILTER_WEBHOOK_FILTERING', true),

        // Cleanup settings
        'auto_cleanup_enabled' => env('PRODUCT_FILTER_AUTO_CLEANUP', true),
        'cleanup_schedule' => env('PRODUCT_FILTER_CLEANUP_SCHEDULE', 'daily'),

        // Webhook configuration for product filter
        'webhooks' => [
            'products/create',
            'products/update',
            'products/delete',
            'collections/create',
            'collections/update',
            'collections/delete',
            'app/scopes_update'
        ],

        // Webhook handler service
        'webhook_handler' => \Bestdecoders\ShopifyLaravelEnhanced\Services\ProductFilterWebhookHandler::class,
    ],

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

    // ===========================================
    // TELEGRAM CONFIGURATION
    // ===========================================

    'telegram' => [
        'enabled' => env('TELEGRAM_ENABLED', false),
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_CHAT_ID'),

        // Telegram webhook URL (automatically generated)
        'webhook_url' => env('TELEGRAM_WEBHOOK_URL'),

        // Telegram commands mapping
        'commands' => [
            // AI Chat Command - Chat with AI assistant
            'ai' => \Bestdecoders\ShopifyLaravelEnhanced\Telegram\Commands\AiChatCommand::class,

            // Discount Command - Manage discount coupons
            'discount' => \Bestdecoders\ShopifyLaravelEnhanced\Telegram\Commands\DiscountCommand::class,

            // Help Command - Show available commands and their usage
            'help' => \Bestdecoders\ShopifyLaravelEnhanced\Telegram\Commands\HelpCommand::class,
        ],
        
    ],

    // ===========================================
    // TAWK.TO CHAT WIDGET CONFIGURATION
    // ===========================================

    'tawk_to' => [
        'enabled' => env('TAWK_TO_ENABLED', true),
        'property_id' => env('TAWK_TO_PROPERTY_ID'),
        'widget_id' => env('TAWK_TO_WIDGET_ID'),

        // Widget position (bottom-right by default)
        // Options: 'bottom-right', 'bottom-left'
        'position' => env('TAWK_TO_POSITION', 'bottom-right'),

        // Auto-hide on mobile
        'hide_on_mobile' => env('TAWK_TO_HIDE_ON_MOBILE', false),

        // Widget opacity (0.1 to 1.0)
        'opacity' => env('TAWK_TO_OPACITY', 1.0),

        // Widget size (small, medium, large)
        'size' => env('TAWK_TO_SIZE', 'medium'),
    ],

    // ===========================================
    // BRAIN AI SERVICE CONFIGURATION
    // ===========================================

    'brain' => [
        // Default AI provider (openai, anthropic)
        'default_provider' => env('BRAIN_DEFAULT_PROVIDER', 'openai'),

        // Default model to use (provider-specific)
        'default_model' => env('BRAIN_DEFAULT_MODEL', 'gpt-4o-mini'),

        // Configuration for each provider
        'providers' => [
            'openai' => [
                'api_key' => env('OPENAI_API_KEY'),
                'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
                'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            ],

            'anthropic' => [
                'api_key' => env('ANTHROPIC_API_KEY'),
                'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com/v1'),
                'model' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'),
            ],
        ],

        // Memory cache settings (in minutes)
        'memory_cache_ttl' => env('BRAIN_MEMORY_TTL', 1440), // 24 hours

        // Default settings
        'default_temperature' => env('BRAIN_DEFAULT_TEMPERATURE', 0.7),
        'default_max_tokens' => env('BRAIN_DEFAULT_MAX_TOKENS', 1000),
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

            'details' => <<<GRAPHQL
                query getProductDetails(\$id: ID!) {
                    product(id: \$id) {
                        id
                        title
                        handle
                        vendor
                        productType
                        status
                        tags
                        updatedAt
                        collections(first: 250) {
                            edges {
                                node {
                                    id
                                }
                            }
                        }
                        variants(first: 1) {
                            edges {
                                node {
                                    price
                                    compareAtPrice
                                    inventoryQuantity
                                }
                            }
                        }
                    }
                }
            GRAPHQL,

            'check_collection_membership' => <<<GRAPHQL
                query checkProductInCollections(\$productId: ID!, \$collectionIds: [ID!]!) {
                    product(id: \$productId) {
                        id
                        collections(first: 250) {
                            edges {
                                node {
                                    id
                                }
                            }
                        }
                    }
                    nodes(ids: \$collectionIds) {
                        ... on Collection {
                            id
                            handle
                            title
                        }
                    }
                }
            GRAPHQL,

            'collection_products' => <<<GRAPHQL
                query getCollectionProducts(\$id: ID!, \$cursor: String, \$pageSize: Int!) {
                    collection(id: \$id) {
                        id
                        products(first: \$pageSize, after: \$cursor) {
                            pageInfo {
                                hasNextPage
                                hasPreviousPage
                                endCursor
                            }
                            edges {
                                node {
                                    id
                                    title
                                    handle
                                    vendor
                                    productType
                                    status
                                    tags
                                    updatedAt
                                    collections(first: 250) {
                                        edges {
                                            node {
                                                id
                                            }
                                        }
                                    }
                                    variants(first: 1) {
                                        edges {
                                            node {
                                                price
                                                compareAtPrice
                                                inventoryQuantity
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            GRAPHQL,

        ],

        'collection' => [
            'details' => <<<GRAPHQL
                query getCollectionDetails(\$id: ID!) {
                    collection(id: \$id) {
                        id
                        handle
                        title
                        updatedAt
                        productsCount
                    }
                }
            GRAPHQL,

            'search' => <<<GRAPHQL
                query searchCollections(\$query: String, \$cursor: String, \$pageSize: Int!) {
                    collections(first: \$pageSize, after: \$cursor, query: \$query) {
                        pageInfo {
                            hasNextPage
                            hasPreviousPage
                        }
                        edges {
                            node {
                                id
                                handle
                                title
                                updatedAt
                                productsCount
                            }
                        }
                    }
                }
            GRAPHQL,
        ],
    ],

];

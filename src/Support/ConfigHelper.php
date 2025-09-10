<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Support;

/**
 * Configuration Helper Class
 * 
 * Provides easy access to package configurations across multiple projects
 */
class ConfigHelper
{
    /**
     * Get a GraphQL query by category and key
     *
     * @param string $category Category (billing, product, customer, etc.)
     * @param string $key Query key (create_recurring_charge, search, etc.)
     * @return string|null
     */
    public static function getGraphQLQuery(string $category, string $key): ?string
    {
        return config("shopify-enhanced-graphql-queries.{$category}.{$key}");
    }

    /**
     * Get all GraphQL queries for a category
     *
     * @param string $category Category name
     * @return array
     */
    public static function getGraphQLCategory(string $category): array
    {
        return config("shopify-enhanced-graphql-queries.{$category}", []);
    }

    /**
     * Get billing configuration
     *
     * @param string $key Configuration key
     * @param mixed $default Default value
     * @return mixed
     */
    public static function getBillingConfig(string $key, $default = null)
    {
        return config("shopify-enhanced-billing.{$key}", $default);
    }

    /**
     * Get subscription pricing for a plan
     *
     * @param string $planType Plan type (monthly, yearly, lifetime)
     * @return array|null
     */
    public static function getPlanDetails(string $planType): ?array
    {
        return config("shopify-enhanced-billing.pricing.{$planType}");
    }

    /**
     * Check if a feature is enabled
     *
     * @param string $feature Feature key
     * @return bool
     */
    public static function isFeatureEnabled(string $feature): bool
    {
        return config("shopify-enhanced-features.enabled.{$feature}", false);
    }

    /**
     * Check if a subscription feature is enabled
     *
     * @param string $feature Subscription feature key
     * @return bool
     */
    public static function isSubscriptionFeatureEnabled(string $feature): bool
    {
        return config("shopify-enhanced-features.subscriptions.{$feature}", false);
    }

    /**
     * Check if a coupon feature is enabled
     *
     * @param string $feature Coupon feature key
     * @return bool
     */
    public static function isCouponFeatureEnabled(string $feature): bool
    {
        return config("shopify-enhanced-features.coupons.{$feature}", false);
    }

    /**
     * Get webhook configuration
     *
     * @param string $key Webhook config key
     * @return mixed
     */
    public static function getWebhookConfig(string $key)
    {
        return config("shopify-enhanced.webhooks.{$key}");
    }

    /**
     * Get user model class
     *
     * @return string
     */
    public static function getUserModel(): string
    {
        return config('shopify-enhanced.user_model', \App\Models\User::class);
    }

    /**
     * Get admin email
     *
     * @return string
     */
    public static function getAdminEmail(): string
    {
        return config('shopify-enhanced.admin_email', 'admin@example.com');
    }

    /**
     * Check if billing test mode is enabled
     *
     * @return bool
     */
    public static function isBillingTestMode(): bool
    {
        return static::getBillingConfig('test_mode', true);
    }

    /**
     * Get billing callback URL
     *
     * @return string
     */
    public static function getBillingCallbackUrl(): string
    {
        return static::getBillingConfig('callback_url', config('app.url') . '/billing/callback');
    }

    /**
     * Get trial days for a plan
     *
     * @param string $planType Plan type
     * @return int
     */
    public static function getTrialDays(string $planType): int
    {
        $planDetails = static::getPlanDetails($planType);
        if ($planDetails && isset($planDetails['trial_days'])) {
            return $planDetails['trial_days'];
        }

        return static::getBillingConfig("trial_days.{$planType}", 
            static::getBillingConfig('trial_days.default', 14)
        );
    }

    /**
     * Get currency settings
     *
     * @param string|null $key Specific currency setting or null for all
     * @return mixed
     */
    public static function getCurrency(string $key = null)
    {
        if ($key) {
            return static::getBillingConfig("currency.{$key}");
        }
        
        return static::getBillingConfig('currency', [
            'default' => 'USD',
            'symbol' => '$',
            'decimal_places' => 2,
        ]);
    }

    /**
     * Get all available GraphQL queries
     *
     * @return array
     */
    public static function getAllGraphQLQueries(): array
    {
        return config('shopify-enhanced-graphql-queries', []);
    }

    /**
     * Get all billing configuration
     *
     * @return array
     */
    public static function getAllBillingConfig(): array
    {
        return config('shopify-enhanced-billing', []);
    }

    /**
     * Get all features configuration
     *
     * @return array
     */
    public static function getAllFeatures(): array
    {
        return config('shopify-enhanced-features', []);
    }

    /**
     * Check if testing features are enabled
     *
     * @return bool
     */
    public static function isTestingEnabled(): bool
    {
        return static::isFeatureEnabled('testing.test_endpoints') || 
               app()->environment(['local', 'testing']);
    }

    /**
     * Get GraphQL service configuration
     *
     * @param string $key Config key
     * @return mixed
     */
    public static function getGraphQLConfig(string $key)
    {
        return config("shopify-enhanced-features.graphql.{$key}", false);
    }

    /**
     * Check if GraphQL logging is enabled
     *
     * @return bool
     */
    public static function isGraphQLLoggingEnabled(): bool
    {
        return static::getGraphQLConfig('query_logging');
    }

    /**
     * Check if GraphQL retry is enabled
     *
     * @return bool
     */
    public static function isGraphQLRetryEnabled(): bool
    {
        return static::getGraphQLConfig('retry_mechanism');
    }

    /**
     * Get webhook handler service class
     *
     * @return string
     */
    public static function getWebhookHandlerService(): string
    {
        return config(
            'shopify-enhanced.webhooks.handler_service',
            'Bestdecoders\\ShopifyLaravelEnhanced\\Services\\WebhookHandlerService'
        );
    }

    /**
     * Get all mandatory webhooks
     *
     * @return array
     */
    public static function getMandatoryWebhooks(): array
    {
        return config('shopify-enhanced.webhooks.mandatory_webhooks', [
            'customers/data_request',
            'customers/redact', 
            'shop/redact'
        ]);
    }

    /**
     * Build configuration array for multiple projects
     * Useful for creating standardized config across projects
     *
     * @param array $overrides Configuration overrides
     * @return array
     */
    public static function buildProjectConfig(array $overrides = []): array
    {
        $defaultConfig = [
            // Core settings
            'admin_email' => static::getAdminEmail(),
            'user_model' => static::getUserModel(),
            
            // Billing settings
            'billing_test_mode' => static::isBillingTestMode(),
            'billing_callback_url' => static::getBillingCallbackUrl(),
            
            // Feature flags
            'features_enabled' => [
                'subscriptions' => static::isFeatureEnabled('subscription_management'),
                'coupons' => static::isFeatureEnabled('coupon_system'),
                'webhooks' => static::isFeatureEnabled('webhook_handlers'),
                'testing' => static::isTestingEnabled(),
            ],
            
            // Pricing
            'pricing' => [
                'monthly' => static::getPlanDetails('monthly'),
                'yearly' => static::getPlanDetails('yearly'),
                'lifetime' => static::getPlanDetails('lifetime'),
            ],
        ];

        return array_merge_recursive($defaultConfig, $overrides);
    }

    /**
     * Validate configuration completeness
     * Useful for checking if all required configs are set
     *
     * @return array Array of missing or invalid configurations
     */
    public static function validateConfiguration(): array
    {
        $issues = [];

        // Check required GraphQL queries
        $requiredQueries = [
            'billing.create_recurring_charge',
            'billing.cancel_subscription',
            'billing.get_app_subscriptions',
        ];

        foreach ($requiredQueries as $queryPath) {
            [$category, $key] = explode('.', $queryPath);
            if (empty(static::getGraphQLQuery($category, $key))) {
                $issues[] = "Missing GraphQL query: {$queryPath}";
            }
        }

        // Check required billing config
        if (empty(static::getPlanDetails('monthly'))) {
            $issues[] = "Missing monthly plan configuration";
        }

        // Check webhook handler
        $handlerClass = static::getWebhookHandlerService();
        if (!class_exists($handlerClass)) {
            $issues[] = "Webhook handler class not found: {$handlerClass}";
        }

        // Check user model
        $userModel = static::getUserModel();
        if (!class_exists($userModel)) {
            $issues[] = "User model class not found: {$userModel}";
        }

        return $issues;
    }
}
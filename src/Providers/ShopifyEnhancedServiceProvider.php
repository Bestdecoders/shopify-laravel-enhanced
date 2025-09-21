<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Providers;

use Illuminate\Support\ServiceProvider;
use Bestdecoders\ShopifyLaravelEnhanced\Services\ShopifyGraphqlService;
use Bestdecoders\ShopifyLaravelEnhanced\Services\SubscriptionManagementService;
use Bestdecoders\ShopifyLaravelEnhanced\Services\ProductFilterService;
use Bestdecoders\ShopifyLaravelEnhanced\Services\ProductFilterWebhookHandler;
use Bestdecoders\ShopifyLaravelEnhanced\Services\ScopeValidationService;

class ShopifyEnhancedServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishConfigurations();
        $this->publishMigrations();
        $this->publishViews();
        $this->publishRoutes();
        $this->registerCommands();
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerConfigurations();
        $this->registerServices();
        $this->registerBindings();
    }

    /**
     * Register package configurations
     */
    protected function registerConfigurations(): void
    {
        // Merge main config
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/shopify-enhanced.php',
            'shopify-enhanced'
        );

        // Register separate config files
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/graphql-queries.php',
            'shopify-enhanced-graphql-queries'
        );

    }

    /**
     * Publish package configurations
     */
    protected function publishConfigurations(): void
    {
        // Main config
        $this->publishes([
            __DIR__ . '/../../config/shopify-enhanced.php' => config_path('shopify-enhanced.php'),
        ], ['config', 'shopify-enhanced-config']);

        // GraphQL queries config
        $this->publishes([
            __DIR__ . '/../../config/graphql-queries.php' => config_path('shopify-enhanced-graphql-queries.php'),
        ], ['config', 'shopify-enhanced-graphql-config']);

        // Publish all configs at once
        $this->publishes([
            __DIR__ . '/../../config/shopify-enhanced.php' => config_path('shopify-enhanced.php'),
            __DIR__ . '/../../config/graphql-queries.php' => config_path('shopify-enhanced-graphql-queries.php'),
        ], ['config', 'shopify-enhanced-all-configs']);
    }

    /**
     * Register package services
     */
    protected function registerServices(): void
    {
        // Register GraphQL service as singleton
        $this->app->singleton('shopify-graphql', function ($app) {
            return new ShopifyGraphqlService();
        });

        // Register Subscription Management Service
        $this->app->singleton(SubscriptionManagementService::class, function ($app) {
            return new SubscriptionManagementService($app->make('shopify-graphql'));
        });

        // Register Product Filter Services
        $this->app->singleton(ScopeValidationService::class);

        $this->app->singleton(ProductFilterService::class, function ($app) {
            return new ProductFilterService($app->make('shopify-graphql'));
        });

        $this->app->singleton(ProductFilterWebhookHandler::class, function ($app) {
            return new ProductFilterWebhookHandler($app->make(ProductFilterService::class));
        });
    }

    /**
     * Register service bindings
     */
    protected function registerBindings(): void
    {
        // Bind GraphQL service interface if exists
        if (interface_exists('Bestdecoders\ShopifyLaravelEnhanced\Contracts\GraphqlServiceInterface')) {
            $this->app->bind(
                'Bestdecoders\ShopifyLaravelEnhanced\Contracts\GraphqlServiceInterface',
                ShopifyGraphqlService::class
            );
        }

        // Bind webhook handler service
        $this->app->bind(
            'shopify-enhanced.webhook-handler',
            config('shopify-enhanced.webhooks.handler_service', 'Bestdecoders\ShopifyLaravelEnhanced\Services\WebhookHandlerService')
        );
    }

    /**
     * Publish migrations
     */
    protected function publishMigrations(): void
    {
        if (is_dir(__DIR__ . '/../../database/migrations')) {
            $this->publishes([
                __DIR__ . '/../../database/migrations' => database_path('migrations'),
            ], ['migrations', 'shopify-enhanced-migrations']);
        }
    }

    /**
     * Publish views
     */
    protected function publishViews(): void
    {
        if (is_dir(__DIR__ . '/../../resources/views')) {
            $this->publishes([
                __DIR__ . '/../../resources/views' => resource_path('views/vendor/shopify-enhanced'),
            ], ['views', 'shopify-enhanced-views']);

            $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'shopify-enhanced');
        }
    }

    /**
     * Publish routes
     */
    protected function publishRoutes(): void
    {
        if (file_exists(__DIR__ . '/../../routes/web.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        }

        // Publish routes for customization
        $this->publishes([
            __DIR__ . '/../../routes/web.php' => base_path('routes/shopify-enhanced.php'),
        ], ['routes', 'shopify-enhanced-routes']);
    }

    /**
     * Register package commands
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $commands = [];

            // Register test data seeder command
            if (class_exists('Bestdecoders\ShopifyLaravelEnhanced\Console\Commands\SeedTestDataCommand')) {
                $commands[] = 'Bestdecoders\ShopifyLaravelEnhanced\Console\Commands\SeedTestDataCommand';
            }

            // Register webhook setup command
            if (class_exists('Bestdecoders\ShopifyLaravelEnhanced\Console\Commands\SetupWebhooksCommand')) {
                $commands[] = 'Bestdecoders\ShopifyLaravelEnhanced\Console\Commands\SetupWebhooksCommand';
            }

            // Register GraphQL query testing command
            if (class_exists('Bestdecoders\ShopifyLaravelEnhanced\Console\Commands\TestGraphQLCommand')) {
                $commands[] = 'Bestdecoders\ShopifyLaravelEnhanced\Console\Commands\TestGraphQLCommand';
            }

            // Register Product Filter cleanup command
            if (class_exists('Bestdecoders\ShopifyLaravelEnhanced\Console\Commands\CleanupOldProductsCommand')) {
                $commands[] = 'Bestdecoders\ShopifyLaravelEnhanced\Console\Commands\CleanupOldProductsCommand';
            }

            if (!empty($commands)) {
                $this->commands($commands);
            }
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            'shopify-graphql',
            SubscriptionManagementService::class,
            'shopify-enhanced.webhook-handler',
        ];
    }
}
<?php

namespace Bestdecoders\ShopifyLaravelEnhanced;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Osiset\ShopifyApp\Messaging\Events\ShopAuthenticatedEvent;
use Bestdecoders\ShopifyLaravelEnhanced\Listeners\RunAfterInstallJob;


class ShopifyEnhancedServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */ public function boot()
    {

        // Load Package Routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Load Package Views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'shopify-enhanced');

        // Load Package Migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // listen to the ShopAuthenticatedEvent
        // if (config('shopify-enhanced.auto_register_install_job', true)) {
        //     Event::listen(
        //         ShopAuthenticatedEvent::class,
        //         RunAfterInstallJob::class
        //     );
        // }
        // === DEFAULT PUBLISHING (Essential components only) ===
        
        // Publish Jobs (Install/Uninstall) with dependencies
        $this->publishes([
            __DIR__ . '/Jobs/AppUninstalledJob.php' => app_path('Jobs/AppUninstalledJob.php'),
        ], ['default', 'shopify-enhanced-jobs']);

        // Publish Mail classes (required by jobs)
        $this->publishes([
            __DIR__ . '/Mail' => app_path('Mail'),
        ], ['default', 'shopify-enhanced-mail']);

        // Publish Services (required by AfterInstallJob)
        $this->publishes([
            __DIR__ . '/Services' => app_path('Services'),
        ], ['default', 'shopify-enhanced-services']);

        // Publish Config (required by jobs and services)
        $this->publishes([
            __DIR__ . '/../config/shopify-enhanced.php' => config_path('shopify-enhanced.php'),
        ], ['default', 'shopify-enhanced-config']);

        // Publish Email Templates (required by Mail classes)
        $this->publishes([
            __DIR__ . '/../resources/views/emails' => resource_path('views/emails'),
        ], ['default', 'shopify-enhanced-emails']);

        // Publish Grandfather Access Commands
        $this->publishes([
            __DIR__ . '/Console/Commands/GrantGrandfatherAccessCommand.php' =>
                app_path('Console/Commands/GrantGrandfatherAccessCommand.php'),
            __DIR__ . '/Console/Commands/RevokeExpiredGrandfatheredAccessCommand.php' =>
                app_path('Console/Commands/RevokeExpiredGrandfatheredAccessCommand.php'),
        ], ['default', 'shopify-enhanced-commands']);

        // === OPTIONAL FEATURE PUBLISHING ===

        // Publish Core Components (shared frontend assets)
        $this->publishes([
            __DIR__ . '/../resources/js/components' => resource_path('js/components'),
            __DIR__ . '/../resources/js/hooks' => resource_path('js/hooks'),
            __DIR__ . '/../resources/js/app.jsx' => resource_path('js/app.jsx'),
            __DIR__ . '/../resources/views/app.blade.php' => resource_path('views/app.blade.php'),
            __DIR__ . '/../resources/views/home.blade.php' => resource_path('views/home.blade.php'),
            __DIR__ . '/../resources/views/emails' => resource_path('views/emails'),
            __DIR__ . '/../resources/css/app.css' => resource_path('css/app.css'),
        ], 'shopify-enhanced-core');

        // Publish FAQ Page
        $this->publishes([
            __DIR__ . '/../resources/js/Pages/Faq.jsx' => resource_path('js/Pages/Faq.jsx'),
            __DIR__ . '/../storage/faq.json' => storage_path('app/faq.json'),
        ], 'shopify-enhanced-faq');

        // Publish Documentation Page
        $this->publishes([
            __DIR__ . '/../resources/js/Pages/Documentation.jsx' => resource_path('js/Pages/Documentation.jsx'),
            __DIR__ . '/../resources/css/documentation.css' => resource_path('css/documentation.css'),
            __DIR__ . '/../storage/docs' => storage_path('app/docs'),
        ], 'shopify-enhanced-docs');

        // Publish Pricing Page and Related Components
        $this->publishes([
            // Pricing Page
            __DIR__ . '/../resources/js/Pages/Pricing.jsx' => resource_path('js/Pages/Pricing.jsx'),
            
            // Pricing Components
            __DIR__ . '/../resources/js/components/Pricing.jsx' => resource_path('js/components/Pricing.jsx'),
            __DIR__ . '/../resources/js/components/TableEditor.jsx' => resource_path('js/components/TableEditor.jsx'),
            __DIR__ . '/../resources/js/components/EditableInput.jsx' => resource_path('js/components/EditableInput.jsx'),
            
            // Billing Configuration
            __DIR__ . '/../config/billing.php' => config_path('billing.php'),
            
            // Pricing Styles
            __DIR__ . '/../resources/css/table-editor.css' => resource_path('css/table-editor.css'),
        ], 'shopify-enhanced-pricing');

        // Publish Middleware
        $this->publishes([
            __DIR__ . '/Middleware' => app_path('Http/Middleware'),
        ], 'shopify-enhanced-middleware');

        // Publish Webhook Handler (for user customization)
        $this->publishes([
            __DIR__ . '/Stubs/CustomWebhookHandler.php' => app_path('Services/CustomWebhookHandler.php'),
        ], 'shopify-enhanced-webhooks');

        // Publish Bestdecoders Home Page
        $this->publishes([
            __DIR__ . '/../resources/views/home.blade.php' => resource_path('views/home.blade.php'),
        ], 'shopify-enhanced-home');

        // Publish Bestdecoders Privacy Policy
        $this->publishes([
            __DIR__ . '/../resources/views/privacy.blade.php' => resource_path('views/privacy.blade.php'),
        ], 'shopify-enhanced-privacy');

        // Publish Exception Handler (with Shopify exception handling)
        $this->publishes([
            __DIR__ . '/Stubs/Handler.php' => app_path('Exceptions/Handler.php'),
        ], ['default', 'shopify-enhanced-exceptions']);

        // Publish Sidebar Component (individual publishing)
        $this->publishes([
            __DIR__ . '/../resources/js/components/sidebar.jsx' => resource_path('js/components/sidebar.jsx'),
        ], 'shopify-enhanced-sidebar');
        
    }


    /**
     * Register any application services.
     */
    public function register()
    {


        $this->mergeConfigFrom(
            __DIR__ . '/../config/shopify-enhanced.php',
            'shopify-enhanced'
        );
        
 
    }
}



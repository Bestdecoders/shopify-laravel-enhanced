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
        // Publish Config File
        $this->publishes([
            __DIR__ . '/../config/shopify-enhanced.php' => config_path('shopify-enhanced.php'),
        ], 'shopify-enhanced-config');


        $this->publishes([
            // Views
            __DIR__ . '/../resources/views/emails' => resource_path('views/emails'),
            __DIR__ . '/../resources/views/app.blade.php' => resource_path('views/app.blade.php'),

            // JS
            __DIR__ . '/../resources/js/hooks' => resource_path('js/hooks'),
            __DIR__ . '/../resources/js/components' => resource_path('js/components'),
            __DIR__ . '/../resources/js/pages' => resource_path('js/pages'),
            __DIR__ . '/../resources/js/app.jsx' => resource_path('js/app.jsx'),

            // CSS
            __DIR__ . '/../resources/css/app.css' => resource_path('css/app.css'),
            __DIR__ . '/../resources/css/table-editor.css' => resource_path('css/table-editor.css'),

            // FAQ Data Files
            __DIR__ . '/../resources/data' => resource_path('data'),
        ], 'shopify-enhanced-assets');




        // Publish Custom Uninstall Job
        $this->publishes([
            __DIR__ . '/Jobs/AppUninstalledJob.php' => app_path('Jobs/AppUninstalledJob.php'),
        ], 'shopify-enhanced-jobs');



        // Publish Middleware 

        $this->publishes([
            __DIR__ . '/Middleware' => app_path('Http/Middleware'),
        ], 'shopify-enhanced-middleware');

        $this->publishes([
            // Grant command
            __DIR__ . '/Console/Commands/GrantGrandfatherAccessCommand.php' =>
                app_path('Console/Commands/GrantGrandfatherAccessCommand.php'),
        
            // Revoke command
            __DIR__ . '/Console/Commands/RevokeExpiredGrandfatheredAccessCommand.php' =>
                app_path('Console/Commands/RevokeExpiredGrandfatheredAccessCommand.php'),
        ], 'shopify-enhanced-commands');

        // Publish Build Configuration Files
        $this->publishes([
            __DIR__ . '/../stubs/vite.config.js' => base_path('vite.config.js'),
            __DIR__ . '/../stubs/vite.config.extension.js' => base_path('vite.config.extension.js'),
            __DIR__ . '/../stubs/package.extension.json' => base_path('package.extension.json'),
        ], 'shopify-enhanced-config');

        // Publish Extension Files
        $this->publishes([
            __DIR__ . '/../resources/extension' => resource_path('extension'),
            __DIR__ . '/../stubs/extensions' => base_path('extensions'),
        ], 'shopify-enhanced-extensions');
        
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



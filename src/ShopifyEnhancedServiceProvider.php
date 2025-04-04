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
        ], 'shopify-enhanced-assets');




        // Publish Custom Uninstall Job
        $this->publishes([
            __DIR__ . '/Jobs/AppUninstalledJob.php' => app_path('Jobs/AppUninstalledJob.php'),
        ], 'shopify-enhanced-jobs');



        // Publish Middleware 

        $this->publishes([
            __DIR__ . '/Middleware' => app_path('Http/Middleware'),
        ], 'shopify-enhanced-middleware');
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


/**
 * 
 * 
 *   'after_authenticate_job' => [

        [
            'job' => \Bestdecoders\ShopifyLaravelEnhanced\Jobs\AfterInstallJob::class,
            'inline' => false, // Dispatch job for later
        ],
    ],
 * // In app/Http/Kernel.php
protected $routeMiddleware = [
    'billable' => \App\Http\Middleware\Billable::class,
    'extract.shop' => \App\Http\Middleware\ExtractShopName::class,
];
 */

<?php

namespace Bestdecoders\ShopifyLaravelEnhanced;
use Illuminate\Support\ServiceProvider;


class ShopifyEnhancedServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */ public function boot()
    {
        // Publish Config File
        $this->publishes([
            __DIR__ . '/../config/shopify-enhanced.php' => config_path('shopify-enhanced.php'),
        ], 'shopify-enhanced-config');

       
        $this->publishes([
            __DIR__ . '/../resources/views/emails' => resource_path('views/emails'),
        ], 'shopify-enhanced-views');

        // Publish Custom Uninstall Job
        $this->publishes([
            __DIR__ . '/Jobs/AppUninstalledJob.php' => app_path('Jobs/AppUninstalledJob.php'),
        ], 'shopify-enhanced-jobs');

        // Load Package Routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Load Package Views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'shopify-enhanced');
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

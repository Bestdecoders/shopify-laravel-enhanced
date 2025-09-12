<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Traits;

use Osiset\ShopifyApp\Exceptions\MissingShopDomainException;
use Throwable;

trait HandlesShopifyExceptions
{
    /**
     * Get additional exception types that should not be reported.
     * 
     * @return array<int, class-string<\Throwable>>
     */
    protected function getShopifyDontReport(): array
    {
        return [
            MissingShopDomainException::class,
        ];
    }

    /**
     * Handle Shopify-specific exceptions.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    protected function renderShopifyException($request, Throwable $exception)
    {
        if ($exception instanceof MissingShopDomainException) {
            // If it's already on /install and still missing -> don't redirect, just return 400
            if ($request->is('install')) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Missing or invalid shop domain',
                        'message' => 'Please access this app through the Shopify admin panel.',
                        'redirect_url' => 'https://apps.shopify.com'
                    ], 400);
                }
                
                return response('Missing or invalid shop domain. Please access this app through the Shopify admin panel.', 400);
            }

            // For JSON requests on other routes
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Shop authentication required',
                    'message' => 'Please authenticate with your Shopify store first.',
                    'redirect_url' => url('/')
                ], 401);
            }

            // For web requests on other routes, redirect to home
            return redirect('/')->with('error', 'Please authenticate with your Shopify store first.');
        }

        return null;
    }

    /**
     * Merge Shopify exceptions with existing dontReport array.
     * Call this method in your Handler constructor or register method.
     */
    protected function mergeShopifyDontReport(): void
    {
        if (property_exists($this, 'dontReport')) {
            $this->dontReport = array_merge($this->dontReport, $this->getShopifyDontReport());
        }
    }
}
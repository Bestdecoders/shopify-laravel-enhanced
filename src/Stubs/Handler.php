<?php

namespace App\Exceptions;

use Bestdecoders\ShopifyLaravelEnhanced\Traits\HandlesShopifyExceptions;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    use HandlesShopifyExceptions;

    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // Merge Shopify exceptions into dontReport array
        $this->mergeShopifyDontReport();

        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Handle Shopify-specific exceptions
        $shopifyResponse = $this->renderShopifyException($request, $exception);
        if ($shopifyResponse !== null) {
            return $shopifyResponse;
        }

        return parent::render($request, $exception);
    }
}
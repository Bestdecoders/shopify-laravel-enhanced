<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Osiset\ShopifyApp\Exceptions\MissingShopDomainException;
use Throwable;

class ShopifyExceptionHandler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        MissingShopDomainException::class,
    ];

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof MissingShopDomainException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Shop authentication required',
                    'message' => 'Please authenticate with your Shopify store first.',
                    'redirect_url' => url('/')
                ], 401);
            }

            return redirect('/')->with('error', 'Please authenticate with your Shopify store first.');
        }

        return parent::render($request, $exception);
    }

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // Register any additional exception handling logic here
    }
}
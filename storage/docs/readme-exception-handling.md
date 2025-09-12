# Shopify Exception Handling

This package provides built-in exception handling for common Shopify-related exceptions to prevent log file bloat and improve user experience.

## Automatic Exception Handling

The package includes automatic handling for:

- `MissingShopDomainException` - Thrown when users access install routes directly without proper Shopify authentication

## Installation Options

### Option 1: Use Complete Handler (Recommended)

The package publishes a complete exception handler that includes Shopify exception handling:

```bash
php artisan vendor:publish --tag=shopify-enhanced-exceptions
```

This will replace your `app/Exceptions/Handler.php` with a version that includes Shopify exception handling.

### Option 2: Add to Existing Handler

If you want to keep your existing exception handler and just add Shopify handling, use the trait:

```php
<?php

namespace App\Exceptions;

use Bestdecoders\ShopifyLaravelEnhanced\Traits\HandlesShopifyExceptions;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    use HandlesShopifyExceptions;

    public function register(): void
    {
        // Merge Shopify exceptions into dontReport array
        $this->mergeShopifyDontReport();

        // Your existing register logic...
    }

    public function render($request, Throwable $exception)
    {
        // Handle Shopify-specific exceptions
        $shopifyResponse = $this->renderShopifyException($request, $exception);
        if ($shopifyResponse !== null) {
            return $shopifyResponse;
        }

        // Your existing render logic...
        return parent::render($request, $exception);
    }
}
```

## What It Does

### Prevents Logging
- `MissingShopDomainException` is added to the `$dontReport` array
- These exceptions won't create log entries, preventing log file bloat

### Graceful User Handling
- **Web Requests**: Redirects users to home page with a friendly error message
- **JSON/API Requests**: Returns a 401 JSON response with error details

### Example Response

**Web Request:**
- Redirects to `/` with flash message: "Please authenticate with your Shopify store first."

**JSON Request:**
```json
{
    "error": "Shop authentication required",
    "message": "Please authenticate with your Shopify store first.",
    "redirect_url": "https://yourapp.com/"
}
```

## Manual Configuration

If you need to customize the behavior, you can extend the `ShopifyExceptionHandler`:

```php
<?php

namespace App\Exceptions;

use Bestdecoders\ShopifyLaravelEnhanced\Exceptions\ShopifyExceptionHandler;

class Handler extends ShopifyExceptionHandler
{
    // Override methods as needed
    public function render($request, Throwable $exception)
    {
        // Custom logic before Shopify handling
        
        return parent::render($request, $exception);
    }
}
```

## Benefits

1. **Reduces Log File Size**: Common authentication exceptions don't create log entries
2. **Better User Experience**: Users get helpful redirects instead of error pages
3. **API-Friendly**: JSON responses for API requests
4. **Zero Configuration**: Works automatically when published
5. **Flexible**: Can be customized or extended as needed
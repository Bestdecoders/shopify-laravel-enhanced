# 🛡️ Exception Handling Setup Guide

> **Automatic Setup Available!** This package provides smart exception handling for Shopify authentication issues. Choose between automatic replacement or manual integration to prevent the "No authenticated user or shop domain" error.

## 🚨 The Problem

When users access your Shopify app's `/install` route directly (without proper Shopify authentication), they encounter:

- **Error**: "No authenticated user or shop domain"
- **Infinite redirects** on the install page
- **Log file bloat** from repeated authentication exceptions
- **Poor user experience** with technical error messages

## ✅ The Solutions

### Option 1: Automatic Handler Replacement (Recommended)

The easiest way is to let the package replace your Handler.php with an enhanced version that includes Shopify exception handling built-in:

```bash
# This replaces your app/Exceptions/Handler.php with enhanced version
php artisan vendor:publish --tag=shopify-enhanced-exceptions
```

**What you get:**
- ✅ **Complete Handler.php** with Shopify exception handling built-in
- ✅ **Zero configuration** - works immediately
- ✅ **Clear comments** explaining what each part does
- ✅ **Extensible** - marked places for your custom logic
- ✅ **Professional code** with proper documentation

### Option 2: Manual Integration (If you have custom Handler logic)

If you have existing custom exception handling that you want to preserve:

## 📋 Manual Integration Steps

### Step 1: Add the Trait

Open your `app/Exceptions/Handler.php` file and add the trait import:

```php
<?php

namespace App\Exceptions;

use Bestdecoders\ShopifyLaravelEnhanced\Traits\HandlesShopifyExceptions;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    use HandlesShopifyExceptions; // ← Add this line

    // Your existing code...
}
```

### Step 2: Merge Exception Configuration

In your `register()` method, add the Shopify exception merging:

```php
public function register(): void
{
    // Merge Shopify exceptions into dontReport array
    $this->mergeShopifyDontReport();

    // Your existing register code...
    $this->reportable(function (Throwable $e) {
        //
    });
}
```

### Step 3: Add Exception Rendering

In your `render()` method, add Shopify exception handling at the beginning:

```php
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
```

## 📝 Complete Handler Example

Here's how your complete `Handler.php` should look:

```php
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
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     */
    protected $dontReport = [
        // Your existing exceptions...
    ];

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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

        // Your existing logic or parent call
        return parent::render($request, $exception);
    }
}
```

## 🎯 What the Trait Does

### For `/install` Route:
- **Direct access** → Returns 400 status with helpful message
- **Prevents infinite redirects** → No more redirect loops
- **Clear user guidance** → "Please access through Shopify admin panel"

### For Other Routes:
- **Web requests** → Redirects to homepage with error flash message
- **JSON/API requests** → Returns proper 401 JSON response with redirect URL

### Exception Logging:
- **Prevents log spam** → `MissingShopDomainException` won't clutter your logs
- **Smart filtering** → Only logs genuine application errors

## 🔍 Response Examples

### Web Request to /install (Direct Access):
```
Status: 400 Bad Request
Content: "Missing or invalid shop domain. Please access this app through the Shopify admin panel."
```

### JSON Request:
```json
{
    "error": "Missing or invalid shop domain",
    "message": "Please access this app through the Shopify admin panel.",
    "redirect_url": "https://apps.shopify.com"
}
```

### Other Routes:
```
Status: 302 Redirect
Location: /
Flash Message: "Please authenticate with your Shopify store first."
```

## 🚀 Benefits

- ✅ **No file replacement** - Your existing Handler code stays intact
- ✅ **Smart route detection** - Different handling for install vs other routes
- ✅ **User-friendly messages** - Clear guidance instead of technical errors
- ✅ **Prevents log bloat** - Authentication issues don't spam logs
- ✅ **API compatible** - Proper JSON responses for AJAX requests
- ✅ **Backward compatible** - Works with existing exception handling

## ⚠️ Important Notes

1. **Manual integration required** - This trait must be added manually to preserve your existing exception handling logic
2. **One-time setup** - Once integrated, it works automatically for all authentication issues
3. **Safe integration** - The trait only handles Shopify-specific exceptions, leaving your custom logic untouched
4. **Development friendly** - Provides clear error messages during development

## 🔧 Troubleshooting

### Still Getting "No authenticated user" Errors?

1. **Check trait import** - Make sure `use HandlesShopifyExceptions;` is added to your Handler class
2. **Verify register() method** - Ensure `$this->mergeShopifyDontReport();` is called
3. **Check render() method** - Make sure `renderShopifyException()` is called first
4. **Clear cache** - Run `php artisan config:clear` and `php artisan cache:clear`

### Handler Class Not Found Error?

Make sure you have the correct namespace import:
```php
use Bestdecoders\ShopifyLaravelEnhanced\Traits\HandlesShopifyExceptions;
```

### Integration Conflicts?

The trait is designed to be non-invasive. If you have existing exception handling:
1. **Keep your existing code** - Just add the trait calls at the beginning of methods
2. **Order matters** - Always call `renderShopifyException()` first in your render method
3. **Return early** - If the trait returns a response, use it and skip other logic

## 📚 Related Documentation

- [Installation Guide](installation.md) - Package setup instructions
- [Getting Started](getting-started.md) - Quick start guide
- [Troubleshooting](troubleshooting.md) - Common issues and solutions

Need help? Check our [troubleshooting guide](troubleshooting.md) or reach out to our support team.
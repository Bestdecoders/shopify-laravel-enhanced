# Bestdecoders Home Page

A beautiful, responsive home page showcasing Bestdecoders' services and contact information.

## Quick Usage (No Publishing Required)

The home page is available immediately after installing the package:

```
http://yourapp.com/bestdecoders
```

## Publishing the Home Page

If you want to customize the home page, publish it to your project:

### 1. Publish the Home Page View

```bash
php artisan vendor:publish --tag=shopify-enhanced-home
```

This will create: `resources/views/bestdecoders/home.blade.php`

### 2. Add Route to Your Project

Add to your `routes/web.php`:

```php
// Option 1: Use as main home page
Route::get('/', function () {
    return view('bestdecoders.home');
});

// Option 2: Use as custom route
Route::get('/home', function () {
    return view('bestdecoders.home');
});

// Option 3: Use with controller
Route::get('/', [HomeController::class, 'index']);
```

### 3. Controller Example (Optional)

Create `app/Http/Controllers/HomeController.php`:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('bestdecoders.home');
    }
}
```

## Customization

After publishing, you can customize the home page by editing:
- `resources/views/bestdecoders/home.blade.php`

### Available Variables (if using controller)

You can pass data to the view:

```php
return view('bestdecoders.home', [
    'company_name' => 'Your Company',
    'tagline' => 'Your Custom Tagline',
    'services' => ['Service 1', 'Service 2'],
    'contact_email' => 'your@email.com'
]);
```

## Features

- ✅ **Responsive Design** - Works on all devices
- ✅ **Modern Styling** - Professional gradient design
- ✅ **Contact Integration** - Links to Bestdecoders website and email
- ✅ **Service Showcase** - Highlights Shopify apps and WordPress plugins
- ✅ **Zero Dependencies** - Pure HTML/CSS, no external libraries
- ✅ **Fast Loading** - Optimized for performance

## Links Included

- **Website**: https://bestdecoders.com/
- **Contact**: https://bestdecoders.com/contact-us/
- **Email**: support@bestdecoders.com

## Usage Examples

### Example 1: Main Landing Page

```php
// routes/web.php
Route::get('/', function () {
    return view('bestdecoders.home');
});
```

### Example 2: About Us Page

```php
// routes/web.php
Route::get('/about', function () {
    return view('bestdecoders.home');
})->name('about');
```

### Example 3: With Custom Data

```php
// routes/web.php
Route::get('/company', function () {
    return view('bestdecoders.home', [
        'company_name' => config('app.name'),
        'custom_message' => 'Welcome to our application!'
    ]);
});
```

## File Structure After Publishing

```
your-project/
├── resources/
│   └── views/
│       └── bestdecoders/
│           └── home.blade.php
└── routes/
    └── web.php (your custom routes)
```

## Mobile Responsive

The home page is fully responsive and includes:
- Mobile-optimized layout
- Touch-friendly buttons
- Readable typography on small screens
- Flexible grid system

---

**Need Help?**
- 📧 Email: support@bestdecoders.com
- 🌐 Website: https://bestdecoders.com/
- 💬 Contact: https://bestdecoders.com/contact-us/
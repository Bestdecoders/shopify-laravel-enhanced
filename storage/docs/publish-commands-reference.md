# 📦 Publish Commands Reference

> **Complete reference guide for all available publish commands and their published files.** Use this chart to understand exactly what gets published with each command.

## 🎯 Quick Reference

| Command | Type | Description | Files Count |
|---------|------|-------------|-------------|
| `default` | Essential | Core functionality required for all installations | 8+ files |
| `shopify-enhanced-core` | Frontend | Complete frontend components and assets | 10+ files |
| `shopify-enhanced-faq` | Feature | FAQ system with search functionality | 2 files |
| `shopify-enhanced-docs` | Feature | Documentation system with markdown rendering | 3 files |
| `shopify-enhanced-pricing` | Feature | Pricing pages and subscription management | 6+ files |
| `shopify-enhanced-support` | Feature | Customer support system | 2 files |
| `shopify-enhanced-sidebar` | Component | Navigation sidebar component | 1 file |
| `shopify-enhanced-home` | Page | Home page template | 1 file |
| `shopify-enhanced-privacy` | Page | Privacy policy template | 1 file |
| `shopify-enhanced-middleware-publish` | Backend | HTTP middleware classes (for customization) | Multiple files |
| `shopify-enhanced-webhooks` | Backend | Webhook handler customization | 1 file |

## 📋 Detailed Publishing Chart

### 🔥 Essential Commands (Always Needed)

#### `default` - Core Package Installation
```bash
php artisan vendor:publish --provider="Bestdecoders\ShopifyLaravelEnhanced\ShopifyEnhancedServiceProvider"
```

**What gets published:**
| File Type | Source | Destination | Purpose |
|-----------|--------|-------------|----------|
| **Job** | `Jobs/AppUninstalledJob.php` | `app/Jobs/AppUninstalledJob.php` | Handles app uninstall cleanup |
| **Mail Classes** | `Mail/` directory | `app/Mail/` | Email notifications (Thanks, Uninstall) |
| **Services** | `Services/` directory | `app/Services/` | Core business logic services |
| **Config** | `config/shopify-enhanced.php` | `config/shopify-enhanced.php` | Package configuration |
| **Email Templates** | `resources/views/emails/` | `resources/views/emails/` | Email template files |
| **Commands** | `Console/Commands/GrantGrandfatherAccessCommand.php` | `app/Console/Commands/` | Admin commands |
| **Commands** | `Console/Commands/RevokeExpiredGrandfatheredAccessCommand.php` | `app/Console/Commands/` | Admin commands |
| **Exception Handler** | `Stubs/Handler.php` | `app/Exceptions/Handler.php` | Enhanced error handling |

**Tags included:** `default`, `shopify-enhanced-jobs`, `shopify-enhanced-mail`, `shopify-enhanced-services`, `shopify-enhanced-config`, `shopify-enhanced-emails`, `shopify-enhanced-commands`, `shopify-enhanced-exceptions`

**Note:** Middleware is now automatically registered as aliases from the package. Use the aliases `enhancer.extract-shop`, `enhancer.billable`, `enhancer.inertia`, `enhancer.product-filter`, and `enhancer.inject-billing` in your routes.

---

### 🎨 Frontend & UI Commands

#### `shopify-enhanced-core` - Complete Frontend Stack
```bash
php artisan vendor:publish --tag=shopify-enhanced-core
```

**What gets published:**
| File Type | Source | Destination | Purpose |
|-----------|--------|-------------|----------|
| **React Components** | `resources/js/components/` | `resources/js/components/` | All reusable UI components |
| **React Hooks** | `resources/js/hooks/` | `resources/js/hooks/` | Custom React hooks (useAxios, etc.) |
| **Main App** | `resources/js/app.jsx` | `resources/js/app.jsx` | Root React application |
| **Blade Template** | `resources/views/app.blade.php` | `resources/views/app.blade.php` | Main app template |
| **Home Template** | `resources/views/home.blade.php` | `resources/views/home.blade.php` | Homepage template |
| **Email Templates** | `resources/views/emails/` | `resources/views/emails/` | All email templates |
| **CSS Styles** | `resources/css/app.css` | `resources/css/app.css` | Main stylesheet |
| **Exception Handler** | `Stubs/Handler.php` | `app/Exceptions/Handler.php` | Enhanced error handling |

**Perfect for:** Complete frontend setup with all components, hooks, and styles.

---

### 🚀 Feature-Specific Commands

#### `shopify-enhanced-faq` - FAQ System
```bash
php artisan vendor:publish --tag=shopify-enhanced-faq
```

**What gets published:**
| File Type | Source | Destination | Purpose |
|-----------|--------|-------------|----------|
| **FAQ Page** | `resources/js/Pages/Faq.jsx` | `resources/js/Pages/Faq.jsx` | FAQ listing page with search |
| **FAQ Data** | `storage/faq.json` | `storage/app/faq.json` | Sample FAQ data structure |

**Features included:** Search functionality, category filtering, expandable answers, priority badges.

---

#### `shopify-enhanced-docs` - Documentation System
```bash
php artisan vendor:publish --tag=shopify-enhanced-docs
```

**What gets published:**
| File Type | Source | Destination | Purpose |
|-----------|--------|-------------|----------|
| **Docs Page** | `resources/js/Pages/Documentation.jsx` | `resources/js/Pages/Documentation.jsx` | Documentation viewer |
| **Docs Styles** | `resources/css/documentation.css` | `resources/css/documentation.css` | Documentation styling |
| **Sample Docs** | `storage/docs/` | `storage/app/docs/` | Sample documentation files |

**Features included:** Markdown rendering, search, navigation tree, responsive design.

---

#### `shopify-enhanced-pricing` - Pricing & Billing
```bash
php artisan vendor:publish --tag=shopify-enhanced-pricing
```

**What gets published:**
| File Type | Source | Destination | Purpose |
|-----------|--------|-------------|----------|
| **Pricing Page** | `resources/js/Pages/Pricing.jsx` | `resources/js/Pages/Pricing.jsx` | Pricing plans display |
| **Pricing Component** | `resources/js/components/Pricing.jsx` | `resources/js/components/` | Pricing card component |
| **Table Editor** | `resources/js/components/TableEditor.jsx` | `resources/js/components/` | Advanced table editor |
| **Editable Input** | `resources/js/components/EditableInput.jsx` | `resources/js/components/` | Inline editing component |
| **Billing Config** | `config/billing.php` | `config/billing.php` | Billing configuration |
| **Table Styles** | `resources/css/table-editor.css` | `resources/css/` | Table editor styles |

**Features included:** Plan comparison, subscription management, billing integration.

---

#### `shopify-enhanced-support` - Customer Support
```bash
php artisan vendor:publish --tag=shopify-enhanced-support
```

**What gets published:**
| File Type | Source | Destination | Purpose |
|-----------|--------|-------------|----------|
| **Support Component** | `resources/js/components/support.jsx` | `resources/js/components/` | Support form with reply display |
| **Support Page** | `resources/js/Pages/Support.jsx` | `resources/js/Pages/` | Complete support page |

**Features included:** Ticket submission, reply system, email notifications, admin commands.

---

### 🧩 Individual Component Commands

#### `shopify-enhanced-sidebar` - Navigation Sidebar
```bash
php artisan vendor:publish --tag=shopify-enhanced-sidebar
```

**What gets published:**
| File Type | Source | Destination | Purpose |
|-----------|--------|-------------|----------|
| **Sidebar Component** | `resources/js/components/sidebar.jsx` | `resources/js/components/` | Main navigation sidebar |

---

#### `shopify-enhanced-home` - Home Page
```bash
php artisan vendor:publish --tag=shopify-enhanced-home
```

**What gets published:**
| File Type | Source | Destination | Purpose |
|-----------|--------|-------------|----------|
| **Home Template** | `resources/views/home.blade.php` | `resources/views/` | Homepage template |

---

#### `shopify-enhanced-privacy` - Privacy Policy
```bash
php artisan vendor:publish --tag=shopify-enhanced-privacy
```

**What gets published:**
| File Type | Source | Destination | Purpose |
|-----------|--------|-------------|----------|
| **Privacy Template** | `resources/views/privacy.blade.php` | `resources/views/` | Privacy policy page |

---

### ⚙️ Backend System Commands

#### `shopify-enhanced-middleware-publish` - HTTP Middleware (Optional)
```bash
php artisan vendor:publish --tag=shopify-enhanced-middleware-publish
```

**What gets published:**
| File Type | Source | Destination | Purpose |
|-----------|--------|-------------|----------|
| **All Middleware** | `Middleware/` directory | `app/Http/Middleware/` | All package middleware classes for customization |

**Includes:** ExtractShopName, Billable, HandleInertiaRequests, ValidateProductFilterScopes, InjectBillingDetails.

**Note:** By default, middleware is served from the package and available as aliases (`enhancer.extract-shop`, `enhancer.billable`, `enhancer.inertia`, `enhancer.product-filter`, `enhancer.inject-billing`). Only publish if you need to customize the middleware classes.

---

#### `shopify-enhanced-webhooks` - Webhook Customization
```bash
php artisan vendor:publish --tag=shopify-enhanced-webhooks
```

**What gets published:**
| File Type | Source | Destination | Purpose |
|-----------|--------|-------------|----------|
| **Webhook Handler** | `Stubs/CustomWebhookHandler.php` | `app/Services/` | Customizable webhook handler |

---

## 🎯 Publishing Strategies

### Strategy 1: Full Installation (Recommended for New Projects)
```bash
# Install everything at once
php artisan vendor:publish --provider="Bestdecoders\ShopifyLaravelEnhanced\ShopifyEnhancedServiceProvider"

# Add core frontend components
php artisan vendor:publish --tag=shopify-enhanced-core

# Add specific features as needed
php artisan vendor:publish --tag=shopify-enhanced-faq
php artisan vendor:publish --tag=shopify-enhanced-docs
php artisan vendor:publish --tag=shopify-enhanced-support
```

### Strategy 2: Minimal Installation (Backend Only)
```bash
# Essential backend functionality only
php artisan vendor:publish --tag=default
```

### Strategy 3: Feature-by-Feature (Gradual Adoption)
```bash
# Start with core
php artisan vendor:publish --tag=default

# Add features one by one as needed
php artisan vendor:publish --tag=shopify-enhanced-faq
# Test and configure...

php artisan vendor:publish --tag=shopify-enhanced-support
# Test and configure...
```

### Strategy 4: Custom Selection
```bash
# Mix and match specific tags
php artisan vendor:publish --tag=shopify-enhanced-core
php artisan vendor:publish --tag=shopify-enhanced-support
php artisan vendor:publish --tag=shopify-enhanced-sidebar
```

## 🔍 File Verification

After publishing, verify your installation:

```bash
# Check published files
ls -la app/Jobs/AppUninstalledJob.php
ls -la app/Mail/
ls -la resources/js/components/
ls -la config/shopify-enhanced.php

# Verify routes are loaded
php artisan route:list | grep -E "(faq|docs|pricing|support)"

# Check console commands
php artisan list | grep -E "(grandfather|support)"
```

## ⚠️ Important Notes

### File Conflicts
- Use `--force` flag to overwrite existing files
- Back up customized files before re-publishing
- Some files may be duplicated in multiple tags

### Dependencies
- `default` tag files are required by most other features
- `shopify-enhanced-core` includes essential frontend dependencies
- Some components depend on others (e.g., support needs sidebar)

### Customization
- Published files become part of your application
- Customize freely after publishing
- Re-publishing will overwrite your changes (use `--force` carefully)

## 🚀 Quick Start Combinations

### E-commerce App with Support
```bash
php artisan vendor:publish --tag=default
php artisan vendor:publish --tag=shopify-enhanced-core
php artisan vendor:publish --tag=shopify-enhanced-support
php artisan vendor:publish --tag=shopify-enhanced-pricing
```

### Documentation-Heavy App
```bash
php artisan vendor:publish --tag=default
php artisan vendor:publish --tag=shopify-enhanced-core
php artisan vendor:publish --tag=shopify-enhanced-docs
php artisan vendor:publish --tag=shopify-enhanced-faq
```

### Minimal Backend Service
```bash
php artisan vendor:publish --tag=default
php artisan vendor:publish --tag=shopify-enhanced-webhooks
```

This reference guide ensures you know exactly what gets published with each command, helping you make informed decisions about which features to include in your Shopify Laravel application.
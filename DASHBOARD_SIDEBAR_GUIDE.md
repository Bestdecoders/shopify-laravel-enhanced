# Dashboard & Sidebar System Guide

The dashboard and sidebar system has been completely redesigned to be generic, configurable, and reusable for any Shopify app.

## Features

### ✅ **Dashboard Features**
- **Configurable Content**: All content loaded from JSON configuration
- **Welcome Cards**: Customizable welcome messages and getting started flows
- **Quick Actions**: Configurable action buttons for common tasks  
- **Stats Cards**: Display key metrics with trends and descriptions
- **Help Cards**: Support and documentation links
- **Responsive Design**: Works perfectly on all screen sizes
- **Loading States**: Professional loading and error handling

### ✅ **Sidebar Features**  
- **JSON-Based Navigation**: All navigation items loaded from configuration
- **Dynamic Icons**: Configurable icons from Shopify Polaris icon set
- **Sub-navigation**: Support for nested navigation items
- **Active State**: Automatic highlighting of current page
- **External Links**: Support for external links (open in new tab)
- **Conditional Items**: Show/hide navigation items based on conditions
- **Loading States**: Graceful loading and error handling

### ✅ **Layout System**
- **AppLayout Component**: Reusable layout with sidebar integration
- **Consistent Spacing**: Standardized spacing and sizing across all pages
- **Responsive Grid**: Automatic responsive behavior
- **Optional Sidebar**: Can disable sidebar for specific pages

## Quick Setup

### 1. Publish Assets
```bash
php artisan vendor:publish --tag=shopify-enhanced-assets
```

### 2. Customize Dashboard
Edit `resources/data/dashboard-config.json`:

```json
{
  "appConfig": {
    "appName": "Your App Name",
    "appDescription": "your app",
    "featureDescription": "the main functionality"
  },
  "dashboard": {
    "welcomeCard": {
      "title": "Welcome to {appName}!",
      "subtitle": "Get started with {appDescription} setup",
      "showGettingStarted": true
    },
    "quickActions": [
      {
        "id": "setup",
        "title": "Complete Setup",
        "description": "Configure your app settings",
        "icon": "SettingsIcon",
        "url": "/setup",
        "primary": true,
        "enabled": true
      }
    ],
    "statsCards": [
      {
        "id": "usage",
        "title": "Total Usage", 
        "value": "0",
        "change": "+0%",
        "trend": "positive"
      }
    ]
  }
}
```

### 3. Customize Sidebar Navigation
Edit `resources/data/sidebar-config.json`:

```json
{
  "appConfig": {
    "appName": "Your App Name"
  },
  "navigation": {
    "primary": [
      {
        "id": "dashboard",
        "url": "/home", 
        "label": "Dashboard",
        "icon": "HomeIcon"
      },
      {
        "id": "setup",
        "label": "Setup",
        "icon": "CodeIcon",
        "subItems": [
          {
            "id": "settings",
            "url": "/about",
            "label": "App Settings"
          }
        ]
      }
    ],
    "secondary": [
      {
        "id": "support",
        "url": "/support",
        "label": "Support", 
        "icon": "QuestionCircleIcon",
        "enabled": true
      }
    ]
  }
}
```

## Configuration Reference

### Dashboard Config Structure

```json
{
  "appConfig": {
    "appName": "string",           // Your app display name
    "appDescription": "string",    // Generic app description
    "featureDescription": "string" // Main feature description
  },
  "dashboard": {
    "welcomeCard": {
      "title": "string",           // Welcome title (supports {appName} placeholder)
      "subtitle": "string",        // Welcome subtitle
      "showGettingStarted": "boolean" // Show getting started button
    },
    "quickActions": [
      {
        "id": "string",            // Unique identifier
        "title": "string",         // Action title
        "description": "string",   // Action description
        "icon": "string",          // Icon name from iconMap
        "url": "string",           // Navigation URL
        "primary": "boolean",      // Primary button styling
        "enabled": "boolean"       // Show/hide action
      }
    ],
    "statsCards": [
      {
        "id": "string",            // Unique identifier
        "title": "string",         // Stat title
        "value": "string",         // Current value
        "change": "string",        // Change indicator (e.g., "+5%")
        "trend": "positive|negative|neutral", // Trend direction
        "description": "string"    // Stat description
      }
    ],
    "helpCard": {
      "title": "string",           // Help section title
      "description": "string",    // Help description
      "actions": [
        {
          "label": "string",       // Button text
          "url": "string",         // Navigation URL
          "primary": "boolean"     // Primary button styling
        }
      ]
    }
  }
}
```

### Sidebar Config Structure

```json
{
  "appConfig": {
    "appName": "string"            // Your app display name
  },
  "navigation": {
    "primary": [
      {
        "id": "string",            // Unique identifier
        "url": "string",           // Navigation URL (optional for items with subItems)
        "label": "string",         // Display text
        "icon": "string",          // Icon name from Polaris icons
        "description": "string",   // Optional description
        "subItems": [              // Optional sub-navigation
          {
            "id": "string",        // Unique identifier
            "url": "string",       // Navigation URL
            "label": "string",     // Display text
            "external": "boolean", // Open in new tab
            "disabled": "boolean"  // Disabled state
          }
        ]
      }
    ],
    "secondary": [
      // Same structure as primary
    ]
  }
}
```

## Available Icons

Icons from `@shopify/polaris-icons`:
- `HomeIcon`
- `SettingsIcon` 
- `CodeIcon`
- `CreditCardIcon`
- `QuestionCircleIcon`
- `ChatIcon`
- `ThumbsUpIcon`
- `NotificationIcon`
- `UploadIcon`
- `ProductIcon`
- `ExternalIcon`

## Using AppLayout Component

```jsx
import AppLayout from "../components/AppLayout";

export default function MyPage() {
    return (
        <AppLayout 
            title="Page Title"
            currentPath="/my-page"
            showSidebar={true}  // Optional, defaults to true
        >
            <Card>
                {/* Your page content */}
            </Card>
        </AppLayout>
    );
}
```

## Pages Structure

All pages now follow a consistent structure:

### ✅ **Updated Pages**
- **Dashboard** (`/home`): New configurable dashboard with stats and quick actions
- **About** (`/about`): Settings page with sidebar navigation
- **Setup** (`/setup`): Setup wizard with sidebar navigation
- **Pricing** (`/pricing`): Pricing plans with sidebar navigation
- **Support** (`/support`): Support center with sidebar navigation
- **FAQ** (`/faq`): FAQ system with sidebar navigation

### **Navigation Flow**
- All navigation happens through the sidebar
- Current page is automatically highlighted
- External links open in new tabs
- Sub-navigation items are properly organized

## Example Customization

For a "Inventory Tracker" app:

**dashboard-config.json**
```json
{
  "appConfig": {
    "appName": "Inventory Tracker",
    "appDescription": "Inventory Tracker",
    "featureDescription": "inventory tracking and management"
  },
  "dashboard": {
    "welcomeCard": {
      "title": "Welcome to Inventory Tracker!",
      "subtitle": "Get started with inventory management setup"
    },
    "quickActions": [
      {
        "id": "sync",
        "title": "Sync Inventory",
        "description": "Synchronize product inventory",
        "icon": "ProductIcon",
        "url": "/sync"
      }
    ],
    "statsCards": [
      {
        "id": "products",
        "title": "Tracked Products",
        "value": "150",
        "trend": "positive"
      }
    ]
  }
}
```

**sidebar-config.json**
```json
{
  "navigation": {
    "primary": [
      {
        "id": "dashboard",
        "url": "/home",
        "label": "Dashboard", 
        "icon": "HomeIcon"
      },
      {
        "id": "inventory",
        "url": "/inventory",
        "label": "Inventory",
        "icon": "ProductIcon"
      }
    ]
  }
}
```

## File Locations

After publishing assets:
- **Dashboard Config**: `resources/data/dashboard-config.json`
- **Sidebar Config**: `resources/data/sidebar-config.json`
- **AppLayout Component**: `resources/js/components/AppLayout.jsx`
- **Dashboard Page**: `resources/js/Pages/Dashboard.jsx`
- **Sidebar Component**: `resources/js/components/sidebar.jsx`

## Benefits

1. **Zero Code Changes**: Complete customization through JSON files
2. **Consistent UX**: Standardized layout and navigation patterns
3. **Responsive Design**: Works on all screen sizes automatically
4. **Professional UI**: Shopify Polaris design system integration
5. **Easy Maintenance**: Content updates don't require code deployments
6. **Reusable**: Works for any type of Shopify app

The dashboard and sidebar system is now **100% configurable** and provides a professional, consistent experience across all pages! 🎉
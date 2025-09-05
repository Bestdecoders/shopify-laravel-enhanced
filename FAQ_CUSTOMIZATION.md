# FAQ System Customization Guide

The FAQ system has been completely redesigned to be data-driven, searchable, filterable, and easily customizable for any Shopify app.

## Features

### ✅ **Advanced Features Added**
- **JSON-based Data Storage**: FAQ content stored in configurable JSON files
- **Real-time Search**: Search through questions, answers, and tags
- **Category Filtering**: Filter by app-specific categories
- **Tag-based Filtering**: Multi-select tag filtering with clickable tags
- **Priority Levels**: High/Medium/Low priority indicators
- **Last Updated Dates**: Track when FAQs were last modified
- **Loading States**: Professional loading and error handling
- **Empty States**: User-friendly no-results messaging
- **Responsive Design**: Works on all screen sizes

## Quick Setup

### 1. Publish Assets
```bash
php artisan vendor:publish --tag=shopify-enhanced-assets
```

This will publish the FAQ data files to `resources/data/`.

### 2. Customize App Configuration
Edit `resources/data/faq-config.json`:

```json
{
  "appConfig": {
    "appName": "Your App Name",
    "appDescription": "your app",
    "featureDescription": "the main functionality"
  },
  "categories": {
    "setup": {
      "title": "Setup & Installation",
      "icon": "⚙️",
      "color": "info"
    },
    "troubleshooting": {
      "title": "Troubleshooting", 
      "icon": "🔧",
      "color": "warning"
    }
  }
}
```

### 3. Customize FAQ Data
Edit `resources/data/faq-data.json` with your app-specific questions:

```json
[
  {
    "id": 1,
    "category": "setup",
    "question": "How do I install your app?",
    "answer": "Follow these steps...",
    "tags": ["installation", "getting-started"],
    "priority": "high",
    "lastUpdated": "2024-01-15"
  }
]
```

## JSON Structure Reference

### FAQ Configuration (`faq-config.json`)
```json
{
  "appConfig": {
    "appName": "string",        // Display name of your app
    "appDescription": "string", // Generic app reference
    "featureDescription": "string" // Main feature description
  },
  "categories": {
    "category_key": {
      "title": "string",    // Category display name
      "icon": "string",     // Emoji or icon
      "color": "string"     // Shopify Polaris color tone
    }
  }
}
```

### FAQ Data (`faq-data.json`)
```json
[
  {
    "id": "number|string",           // Unique identifier
    "category": "string",            // Category key from config
    "question": "string",            // The FAQ question
    "answer": "string",              // The FAQ answer
    "tags": ["string"],              // Array of searchable tags
    "priority": "high|medium|low",   // Optional priority level
    "lastUpdated": "YYYY-MM-DD"      // Optional last update date
  }
]
```

## Category Colors

Use Shopify Polaris color tones:
- `info` (blue)
- `success` (green) 
- `warning` (yellow)
- `critical` (red)
- `subdued` (gray)

## Advanced Features

### Search Functionality
- Searches through questions, answers, and tags
- Real-time filtering as you type
- Case-insensitive matching

### Tag System
- Clickable tags for instant filtering
- Multi-select tag filtering
- Visual indication of selected tags

### Category System
- Visual category badges with icons
- One-click category filtering
- Question count per category

### Priority System
- `high`: Red critical badge
- `medium`: Yellow warning badge  
- `low`: Blue info badge

## Example Customization

For a "Product Reviews" app:

**faq-config.json**
```json
{
  "appConfig": {
    "appName": "Review Master",
    "appDescription": "Review Master",
    "featureDescription": "product reviews and ratings"
  },
  "categories": {
    "reviews": {
      "title": "Managing Reviews",
      "icon": "⭐",
      "color": "success"
    },
    "display": {
      "title": "Review Display",
      "icon": "👁️",
      "color": "info"
    }
  }
}
```

**faq-data.json**
```json
[
  {
    "id": 1,
    "category": "reviews",
    "question": "How do I moderate customer reviews?",
    "answer": "You can moderate reviews through the admin panel...",
    "tags": ["moderation", "admin", "approval"],
    "priority": "high",
    "lastUpdated": "2024-01-15"
  }
]
```

## File Locations

After publishing assets:
- **FAQ Config**: `resources/data/faq-config.json`
- **FAQ Data**: `resources/data/faq-data.json`
- **FAQ Component**: `resources/js/Pages/FAQ.jsx`

## No Code Changes Required

Once you customize the JSON files, the FAQ system automatically:
- Updates all text references to use your app name
- Loads your categories and styling
- Displays your FAQ data with full search/filter functionality
- Maintains all responsive design and UX features

The FAQ component is completely reusable across different apps with zero code modifications!
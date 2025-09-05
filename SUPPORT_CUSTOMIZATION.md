# Support Component Customization Guide

The support component has been generalized to work with any Shopify app. All app-specific content has been moved to a configuration section for easy customization.

## Quick Setup

To customize the support component for your app, simply edit the `APP_CONFIG` object at the top of `/resources/js/components/support.jsx`:

```javascript
const APP_CONFIG = {
    appName: "Your App Name", // REQUIRED: Change this to your app's display name
    supportEmail: "support@yourdomain.com", // REQUIRED: Your support email address  
    whatsappNumber: "1234567890", // OPTIONAL: Your WhatsApp number (without + or country code)
    appDescription: "your app", // REQUIRED: Short generic reference to your app
    featureDescription: "the main functionality", // REQUIRED: Description of your app's main feature
};
```

## Configuration Fields

### Required Fields
- **appName**: The display name of your app (shown in headers and titles)
- **supportEmail**: Your support email address (used for email links)
- **appDescription**: Generic reference to your app in sentences
- **featureDescription**: Description of your app's main functionality

### Optional Fields
- **whatsappNumber**: Your WhatsApp number without country code or '+' symbol

## What Gets Updated

When you modify the configuration, the following elements will automatically update:

### Text References
- Support page title: "We're here to help you succeed with [YOUR APP NAME]"
- Feature descriptions in form text
- Support commitment message
- Form placeholders and help text

### Contact Information
- Email support links and display text
- WhatsApp chat links
- All mailto: links

### Form Content
- Request form placeholders become contextual to your app
- Help text references your specific functionality

## Example Customization

For a "Size Chart" app, you would set:

```javascript
const APP_CONFIG = {
    appName: "Size Chart Pro", 
    supportEmail: "support@sizechartpro.com", 
    whatsappNumber: "1234567890", 
    appDescription: "Size Chart Pro", 
    featureDescription: "size charts and fitting guides", 
};
```

This would result in text like:
- "We're here to help you succeed with Size Chart Pro"
- "Please describe what you'd like size charts and fitting guides to look like"

## No Other Changes Needed

Once you update the `APP_CONFIG` object, the entire support component will automatically use your app's information. No other modifications are required in the component code.
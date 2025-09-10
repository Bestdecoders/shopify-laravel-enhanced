@component('mail::message')
# Welcome to {{ config('app.name') }}! 🎉

Hello {{ $shop['name'] ?? 'there' }},

Thank you for installing our Shopify app! We're thrilled to have you on board and excited to help your business grow to new heights.

@component('mail::panel')
**Your App Installation Details:**  
Shop: {{ $shop['name'] ?? 'N/A' }}  
Domain: {{ $shop['domain'] ?? $shop['myshopify_domain'] ?? 'N/A' }}  
Installation Date: {{ now()->format('F j, Y \a\t g:i A T') }}
@endcomponent

## What's Next?

Here's how to get the most out of our app:

**1. Complete Your Setup** ⚙️  
Configure your preferences to match your business needs.

**2. Explore Features** 🚀  
Discover all the powerful tools available to boost your sales.

**3. Get Support** 💬  
Our team is here to help you succeed every step of the way.

@component('mail::button', ['url' => "https://{$shop['myshopify_domain']}/admin/apps"])
Open App Dashboard
@endcomponent

## Why Merchants Love Us

✅ **Easy Setup** - Get started in minutes, not hours  
✅ **24/7 Support** - Our team is always here to help  
✅ **Regular Updates** - New features added monthly  
✅ **Proven Results** - Join thousands of successful merchants  

## Need Help Getting Started?

Our support team is standing by to ensure you have a smooth experience. Don't hesitate to reach out!

@component('mail::button', ['url' => 'mailto:support@bestdecoders.com?subject=New%20Installation%20Help'])
Contact Support
@endcomponent

Thank you for choosing {{ config('app.name') }}. We can't wait to see your business thrive!

Best regards,  
The {{ config('app.name') }} Team

---
*Follow us for updates and tips: [Website]({{ config('app.url') }}) | [Documentation]({{ config('app.url') }}/docs)*
@endcomponent


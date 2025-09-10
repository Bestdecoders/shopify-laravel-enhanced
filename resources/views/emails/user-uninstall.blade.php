@component('mail::message')
# Thank You for Trying Our App

Hello,

We wanted to confirm that you have successfully uninstalled our app from your Shopify store:

@component('mail::panel')
**Shop Domain:** {{ $shopDomain }}  
**Uninstall Date:** {{ now()->format('F j, Y \a\t g:i A T') }}
@endcomponent

We're sorry to see you go! Your feedback is valuable to us and helps improve our service. If you experienced any issues or have suggestions, we'd love to hear from you.

@component('mail::button', ['url' => 'mailto:support@bestdecoders.com?subject=Feedback%20for%20' . urlencode(config('app.name'))])
Share Feedback
@endcomponent

**Why did merchants love our app:**
- Easy to use interface
- Excellent customer support
- Regular feature updates
- Competitive pricing

If you decide to give us another try in the future, we'll be here to help you succeed.

Thank you for choosing {{ config('app.name') }}, and we hope to serve you again soon.

Best regards,  
{{ config('app.name') }} Team

---
*Need help with something else? Visit our [knowledge base]({{ config('app.url') }}/help) or contact our support team.*
@endcomponent

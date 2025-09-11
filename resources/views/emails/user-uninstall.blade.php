@component('mail::message')
# Thank You for Trying Our App 👋

Hello there,

We wanted to confirm that you have successfully uninstalled our app from your Shopify store. We're sorry to see you go!

@component('mail::panel')
**Shop Domain:** {{ $shopDomain }}  
**Uninstall Date:** {{ now()->format('F j, Y \a\t g:i A T') }}  
**Status:** App Successfully Removed
@endcomponent

## We Value Your Feedback 💬

Your experience matters to us! If you experienced any issues or have suggestions for improvement, we'd love to hear from you.

@component('mail::button', ['url' => 'mailto:support@bestdecoders.com?subject=Feedback%20for%20' . urlencode(config('app.name'))])
Share Your Feedback
@endcomponent

## What Our Merchants Love About Us ❤️

- ⚡ **Easy Setup** - Quick installation and configuration
- 🎯 **Excellent Support** - Responsive customer service team  
- 🚀 **Regular Updates** - New features added monthly
- 💰 **Great Value** - Competitive pricing with powerful features

## Come Back Anytime 🤝

If you decide to give us another try in the future, we'll be here to help you succeed. We're constantly improving our app based on merchant feedback.

## Questions or Need Help?

Our support team is always ready to assist, even after uninstalling:

- 📚 **Knowledge Base**: [{{ config('app.url') }}/docs]({{ config('app.url') }}/docs)
- 💬 **Live Chat**: Available on our website
- 📧 **Email Support**: support@bestdecoders.com

Thank you for choosing {{ config('app.name') }}. We hope to serve you again soon!

Best regards,  
The {{ config('app.name') }} Team

---
*This is an automated message. Your data has been processed according to our privacy policy.*
@endcomponent

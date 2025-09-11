@component('mail::message')
# App Uninstall Notification 📢

Hello Admin,

We wanted to notify you that our app has been uninstalled from a Shopify store.

@component('mail::panel')
**Shop Domain:** {{ $shopDomain }}  
**Uninstall Date:** {{ now()->format('F j, Y \a\t g:i A T') }}  
**Time:** {{ now()->format('H:i:s T') }}
@endcomponent

## Action Required

This is an automated notification to keep you informed about app uninstall activities. Please consider:

- 📊 Review uninstall metrics and trends
- 📧 Consider sending a follow-up survey
- 🔍 Analyze potential improvement opportunities
- 📞 Reach out to the merchant if appropriate

@component('mail::button', ['url' => config('app.url')])
View Admin Dashboard
@endcomponent

## Contact Information

For questions regarding this uninstall notification, please review your admin panel or contact the development team.

Best regards,  
The {{ config('app.name') }} Team

---
*This is an automated notification from {{ config('app.name') }}*
@endcomponent

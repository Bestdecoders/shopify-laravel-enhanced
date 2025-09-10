@component('mail::message')
# App Uninstall Notification

Hello Admin,

We wanted to notify you that our app has been uninstalled from the following Shopify store:

@component('mail::panel')
**Shop Domain:** {{ $shopDomain }}  
**Uninstall Date:** {{ now()->format('F j, Y \a\t g:i A T') }}
@endcomponent

This is an automated notification to keep you informed about app uninstall activities. Please review the uninstall metrics and consider reaching out to the merchant if appropriate.

@component('mail::button', ['url' => config('app.url')])
View Dashboard
@endcomponent

Best regards,  
{{ config('app.name') }} Team
@endcomponent

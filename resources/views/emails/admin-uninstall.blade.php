@component('mail::message')
# App Uninstalled

The app was uninstalled from the following shop:

**Shop Domain:** {{ $shopDomain }}

Thank you,  
{{ config('app.name') }}
@endcomponent

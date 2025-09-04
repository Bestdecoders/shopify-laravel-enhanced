@component('mail::message')
# Thank You for Using Our App

Hello,

You have successfully uninstalled our app from your shop:

**Shop Domain:** {{ $shopDomain }}

If you have any feedback, please let us know. We hope to serve you again in the future.

Thank you,  
{{ config('app.name') }}
@endcomponent

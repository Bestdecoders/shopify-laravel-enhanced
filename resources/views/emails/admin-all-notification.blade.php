@component('mail::message')

@if(count($data) > 0)
# Shop Info
@foreach($data as $key => $value)
**{{ ucfirst($key) }}:** {{ $value }}
@endforeach
@endif

**Shop Domain:** {{ $shopDomain }}

Thank you,  
{{ config('app.name') }}
@endcomponent

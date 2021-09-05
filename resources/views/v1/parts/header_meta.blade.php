@php
/**
 * Comment: Separating to make cleaner
 * Created: 5/28/2017
 */
@endphp
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-K4MGRCX');</script>
<!-- End Google Tag Manager -->
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
<meta name="description" content="Integrated Member Management, Email Marketing, Event Registration, Surveys">
<meta name="author" content="mCentric / Efcico Corporation">
<meta name="csrf-token" content="{{ csrf_token() }}" />
@if(Auth::user())
    @if(!auth()->user()->remember_token)
        <meta http-equiv="refresh" content="3600;url={{ env('APP_URL') . "/logout" }}" />
    @endif
@endif
@if(env('APP_ENV') == 'local')
    <link href="{{ str_replace('https', 'http', env('APP_URL')) }}/images/mCentric_dev.ico" rel="icon"/>
@else
    <link href="{{ str_replace('https', 'http', env('APP_URL')) }}/images/mCentric.ico" rel="icon"/>
@endif
<base href="{{ env('APP_URL') }}">

@component('mail::layout')
{{-- Header --}}
@if(isset($orgURL) && isset($logoPath))
@slot('header')
@component('mail::header', ['url' => $orgURL])
@if(isset($logoPath))
<img src="{{ $logoPath }}" height="50">
@endif
@endcomponent
@endslot
@endif

{{-- Body --}}
{{ Illuminate\Mail\Markdown::parse($slot) }}

{{-- Subcopy --}}
@if (isset($subcopy))
@slot('subcopy')
@component('mail::subcopy')
{{ $subcopy }}
@endcomponent
@endslot
@endif

{{-- Footer --}}
@slot('footer')
@component('mail::footer')
<a href="{{ config('app.url') }}"><img src="{{ env('APP_URL') }}/images/mCentric_logo_blue.png" height="30"></a>
@endcomponent
@endslot
@endcomponent

@php
    /**
     * Comment: a Two-Button Notification Template originated for Receipt Notifications
     * Created: 2/28/2019
     *
     */

    error_reporting(-1);
    ini_set('display_errors', 'On');
    set_error_handler("var_dump");

    if(isset($name)){
        $greeting = trans('messages.notifications.hello', ['firstName' => $name]);
    }
@endphp

@component('mail::message')
{{-- Greeting --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level == 'error')
# Whoops!
@else
# Hello!
@endif
@endif

{{-- Intro Lines --}}
{!! $line1 or null !!}

{{-- Action Button 1 --}}
@if (isset($action1))
@php
            switch ($c1) {
                case 'success':
                    $color = 'green';
                    break;
                case 'error':
                    $color = 'red';
                    break;
                default:
                    $color = 'blue';
            }
@endphp
@component('mail::button', ['url' => $url1, 'color' => $color])
{{ $action1 or null }}
@endcomponent
@endif

@if(isset($postRegInfo))
@component('mail::promotion')
# {!! trans('messages.notifications.RegNote.postRegHeader') !!}

{!! $postRegInfo !!}
@endcomponent

{!! $line2 or null !!}<br/>

@endif
{{-- Action Button 2 --}}
@if (isset($action2))
@php
            switch ($c2) {
                case 'success':
                    $color = 'green';
                    break;
                case 'error':
                    $color = 'red';
                    break;
                default:
                    $color = 'blue';
            }
@endphp

@component('mail::button', ['url' => $url2, 'color' => $color])
{{ $action2 or null }}
@endcomponent
@endif

{!! $line3 or null !!}

<!-- Salutation -->
@if (! empty($salutation))
{{ $salutation }}
@else
@lang('messages.notifications.regards'),<br>{{ config('app.name') }}
@endif

<!-- Subcopy -->
@if (isset($action1) || isset($action2))
@component('mail::subcopy')
@if(isset($action1))
@lang('messages.notifications.disclaimer', ['a' => $action1, 'u' => $url1])
@endif
@if(isset($action2))
@lang('messages.notifications.disclaimer', ['a' => $action2, 'u' => $url2])
@endif
@endcomponent
@endif
@endcomponent

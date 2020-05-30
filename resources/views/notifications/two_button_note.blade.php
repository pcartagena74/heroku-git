@php
    /**
     * Comment: a Two-Button Notification Template originated for Receipt Notifications
     * Created: 2/28/2019
     * @var $action1
     * @var $action2
     * @var $line1
     * @var $line2
     * @var $line3
     * @var $c1
     * @var $c2
     * @var $postRegInfo
     * @var $name
     *
     */

    error_reporting(-1);
    ini_set('display_errors', 'On');
    set_error_handler("var_dump");

    if(isset($name)){
        $greeting = trans('messages.notifications.hello', ['firstName' => $name]);
    }
    if($action1){
            switch ($c1) {
                case 'success':
                    $color1 = 'green';
                    break;
                case 'error':
                    $color1 = 'red';
                    break;
                default:
                    $color1 = 'blue';
            }
    }
    if($action2){
            switch ($c2) {
                case 'success':
                    $color2 = 'green';
                    break;
                case 'error':
                    $color2 = 'red';
                    break;
                default:
                    $color2 = 'blue';
            }
    }
    //dd('2BN', get_defined_vars())
@endphp
@component('mail::message', ['orgURL' => $orgURL, 'logoPath' => $logoPath])
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
<p></p>
{!! $line1 ?? '' !!}

@if (isset($action1))
@component('mail::button', ['url' => $url1, 'color' => $color1])
{{ $action1 ?? null }}
@endcomponent
@endif

@if(isset($postRegInfo))
@component('mail::promotion')
# {!! trans('messages.notifications.RegNote.postRegHeader') !!}
{!! $postRegInfo !!}
@endcomponent

{!! $line2 ?? null !!}<br/>

@endif
@if (isset($action2))
@component('mail::button', ['url' => $url2, 'color' => $color2])
{{ $action2 ?? null }}
@endcomponent
@endif

{!! $line3 ?? null !!}

@if (! empty($salutation))
{{ $salutation }}
@else
@lang('messages.notifications.regards'),<br>{{ config('app.name') }}
@endif

<!-- Subcopy -->
@if (isset($action1) || isset($action2))
@component('mail::subcopy')
@if(isset($action1))
<p> @lang('messages.notifications.disclaimer', ['a' => $action1, 'u' => $url1]) </p>
@endif
@if(isset($action2))
<p> @lang('messages.notifications.disclaimer', ['a' => $action2, 'u' => $url2]) </p>
@endif
@endcomponent
@endif
@endcomponent

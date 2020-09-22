<?php
/**
 * Comment: a Two-Button Notification Template originated for Receipt Notifications
 * Created: 2/28/2019
 *
 */

error_reporting(-1);
ini_set('display_errors', 'On');
set_error_handler("var_dump");

if(isset($name)){
    $greeting = "Hello $name!";
}
?>
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
{!! $line1 ?? '' !!}

{{-- Action Button 1 --}}
@if (isset($action1))
<?php
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
?>
@component('mail::button', ['url' => $url1, 'color' => $color])
{{ $action1 ?? '' }}
@endcomponent
@endif

@if(isset($postRegInfo))
@component('mail::promotion')
# {!! trans('messages.notifications.RegNote.postRegHeader') !!}

{!! $postRegInfo !!}
@endcomponent
@endif
    {{-- Action Button 2 --}}
@if (isset($action2))
<?php
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
?>
@component('mail::button', ['url' => $url2, 'color' => $color])
    {{ $action2 ?? '' }}
@endcomponent
@endif



{!! $line2 ?? '' !!}<br />
{!! $line3 ?? '' !!}

<!-- Salutation -->
@if (! empty($salutation))
{{ $salutation }}
@else
Regards,<br>{{ config('app.name') }}
@endif

<!-- Subcopy -->
@if (isset($action1) || isset($action2))
@component('mail::subcopy')
@if(isset($action1))
If you’re having trouble clicking the "{{ $action1 }}" button, copy and paste the URL below
into your web browser: [{{ $action1 }}]({{ $url1 }})
@endif

@if(isset($action1))
If you’re having trouble clicking the "{{ $action2 }}" button, copy and paste the URL below
into your web browser: [{{ $action2 }}]({{ $url2 }})
@endif
@endcomponent
@endif
@endcomponent

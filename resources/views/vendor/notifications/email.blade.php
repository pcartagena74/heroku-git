@php
    /**
     * Comment: Default template for notifications
     *          Added php below on 3/1/2019
     */

    if(isset($name)){
        $greeting =  trans('messages.notifications.hello', ['firstName' => $name]); // "Hello $name!";
    }
// If you’re having trouble clicking the "{{ $actionText }}" button, copy and paste the URL below
// into your web browser: [{{ $actionUrl }}]({{ $actionUrl }})
@endphp


@component('mail::message')
    {{-- Greeting --}}
    @if (! empty($greeting))
        # {{ $greeting }}!
    @else
        @if ($level == 'error')
            # Whoops!
        @else
            # Hello!
        @endif
    @endif

    {{-- Intro Lines --}}
    @foreach ($introLines as $line)
        {!! $line !!}

    @endforeach

    {{-- Action Button --}}
    @if (isset($actionText))

        @php
            switch ($level) {
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

        @component('mail::button', ['url' => $actionUrl, 'color' => $color])
            {{ $actionText }}
        @endcomponent
    @endif

    {{-- Outro Lines --}}
    @foreach ($outroLines as $line)
        {!! $line !!}

    @endforeach

    <!-- Salutation -->
    @if (! empty($salutation))
        {{ $salutation }}
    @else
        Regards,<br>{{ config('app.name') }}
    @endif

    <!-- Subcopy -->
    @if (isset($actionText))
        @component('mail::subcopy')
            @lang('messages.notifications.disclaimer', ['a' => $actionText, 'u' => $actionUrl])
        @endcomponent
    @endif
@endcomponent

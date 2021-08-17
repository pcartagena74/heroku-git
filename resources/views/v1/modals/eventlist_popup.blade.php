@php
    /**
     * Comment: Used to display /eventlist/{orgID}/{etID}
     * Created: 11/28/2017
     * @var $events
     * @var $etID
     * @var $tag
     * @var $admin_props
     */

    $ban_bkgd = $admin_props[8]->value;
    $ban_text = $admin_props[9]->value;

    function truncate_saw($string, $limit, $break = ".", $pad = "...")
    {
        if (strlen($string) <= $limit) return $string;
        if (false !== ($max = strpos($string, $break, $limit))) {
            if ($max < strlen($string) - 1) {
                $string = substr($string, 0, $max) . $pad;
            }
        }
        return $string;
    }

    if (null === $etID || preg_match('/,/', $etID)) {
        $event_tag = $tag;
    } else {
        $event_tag = $tag->etName;
    }

@endphp

@if($cnt > 0)
    <table class="table table-bordered table-striped table-sm condensed jambo_table" width="100%" id="eventlisting">
        <thead>
        <tr>
            <td><b>{{ $org->orgName }}: {{ $event_tag }}</b></td>
        </tr>
        </thead>
        <tbody>
        @foreach($events as $e)
            <tr>
                <td>
                    @include('v1.parts.api_one-event', ['e' => $e, 'props' => $admin_props])
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    @lang('messages.messages.no_events', ['which' => strtolower(trans_choice('messages.var_words.time_period', $past))])
@endif


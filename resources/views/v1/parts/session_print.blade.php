<?php
/**
 * Comment: Repeatable session display blade called when session choice display is required
 * Created: 9/29/2018
 *
 * @param $event:
 * @param $ticket
 * @param $reg: the individual registration for which session display is required
 */

use App\EventSession;
use App\RegSession;
use App\Ticket;
?>

@if(1)
    <table class="table table-bordered jambo_table table-striped">
        <thead>
        <tr>
            <th colspan="2" style="text-align: left;">
                @lang('messages.fields.track_select')
            </th>
        </tr>
        </thead>
        <tr>
            <th style="text-align:left;">@lang('messages.fields.sess_times')</th>
            <th style="text-align:left;">@lang('messages.fields.selected_sess')</th>
        </tr>
        @for($j=1;$j<=$event->confDays;$j++)
<?php
            $rs = RegSession::where([
                ['confDay', '=', $j],
                ['regID', '=', $reg->regID],
                ['personID', '=', $reg->personID],
                ['eventID', '=', $event->eventID]
            ])->orderBy('id')->get();
?>

            @foreach($rs as $z)
                @if($rs->first() == $z)
<?php
                    $s = EventSession::find($z->sessionID);
                    $y = Ticket::find($s->ticketID);
?>
                    <tr>
                        <th style="text-align:center; color: yellow; background-color: #2a3f54;"
                            colspan="2">@lang('messages.headers.day') {{ $j }}:
                            {{ $y->ticketLabel  }}
                        </th>
                    </tr>
                @endif
<?php
                $s = EventSession::with('track')->where('sessionID', $z->sessionID)->first();
?>
                <tr>
                    <td rowspan="1" style="text-align:left; width:33%;">
                        <nobr> {{ $s->start->format('g:i A') }} </nobr>
                        -
                        <nobr> {{ $s->end->format('g:i A') }} </nobr>
                    </td>
                    <td colspan="1" style="text-align:left; min-width:150px; width: 67%;">
                        <b>{{ $s->track->trackName }}</b><br />
                        {{ $s->sessionName }} <br/>
                    </td>
                </tr>
            @endforeach
        @endfor
    </table>

@endif
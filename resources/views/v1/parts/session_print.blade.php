<?php
/**
 * Comment: Repeatable session display blade called when read-only display of session selections is required
 * Created: 9/29/2018
 *
 * @param $event :
 * @param $ticket
 * @param $reg : the individual registration for which session display is required
 */

use App\Models\EventSession;
use App\Models\RegSession;
use App\Models\Ticket;

$check = RegSession::where([
    ['regID', $reg->regID],
    ['eventID', $event->eventID],
    ['personID', $reg->personID]
])->get();
?>

@if(count($check) > 0)
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
            ])->orderBy('id')->distinct()->get();
            ?>

            @foreach($rs as $z)
                @if($rs->first() == $z)
                    {{--  Print the Conference Day Label just once --}}
                    <?php
                    $s = EventSession::find($z->sessionID);
                    $y = Ticket::find($s->ticketID);
                    ?>
                    @if(null !== $s)
                        <tr>
                            <th style="text-align:center; color: yellow; background-color: #2a3f54;"
                                colspan="2">@lang('messages.headers.day') {{ $j }}:
                                {{ $y->ticketLabel  }}
                            </th>
                        </tr>
                    @endif
                @endif
                <?php
                $s = EventSession::with('track')->where('sessionID', $z->sessionID)->first();
                ?>
                @if($s)
                    <tr>
                        <td rowspan="1" style="text-align:left; width:33%;">
                            <nobr> {{ $s->start->format('g:i A') }} </nobr>
                            -
                            <nobr> {{ $s->end->format('g:i A') }} </nobr>
                        </td>
                        <td colspan="1" style="text-align:left; min-width:150px; width: 67%;">
                            <b>{{ $s->track->trackName }}</b><br/>
                            {{ $s->sessionName }} <br/>
                        </td>
                    </tr>
                @endif
            @endforeach
        @endfor
    </table>
@else
    <div class="form-group">
        <div class="alert alert-warning col-xs-12">
            @lang('messages.messages.no_sessions', ['link' => env('APP_URL')."/upcoming"])
        </div>
    </div>
@endif

<?php
/**
 * Comment: Track and Session Setup for PD Day Events
 * Created: 4/11/2017
 */
use App\EventSession;
use App\Ticket;

$topBits = '';

if($event->isSymmetric) {
    $columns = ($event->hasTracks * 2) + 1;
    $width   = (integer) 85/$event->hasTracks;
    $mw   = (integer) 90/$event->hasTracks;
} else {
    $columns = $event->hasTracks * 3;
    $width   = (integer) 80/$event->hasTracks;
    $mw   = (integer) 85/$event->hasTracks;
}

$tickets = Ticket::where([
    ['eventID', $event->eventID],
    ['isaBundle', '=', 0]
])->get();

?>

@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    @include('v1.parts.start_content', ['header' => 'Track & Session Setup: ' . $event->eventName, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    @include('v1.parts.start_content', ['header' => 'Session Setup Questions and Instructions', 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
    <div class="col-sm-6">
        <ol>
            <li>If the majority of your times are standard for the event tracks, choose Yes for now. <br/>
                You can change this later to edit any unique track/session times.
            </li>
            <li>Edit the number of days the event will run.</li>
            <li>Edit the times and other information for each session where attendees have a choice. No need to enter
                Keynotes, lunches, etc. A Delete unnecessary sessions.
            </li>
            <li>PDU values are calculated based on the end date/time - start date/time.</li>
            <li>Leave Session Occupancy at 0 if there are no hard limits for registration.</li>
        </ol>
    </div>
    <div class="col-sm-3">
        {!! Form::open(array('url' => '/tracksymmetry/'.$event->eventID, 'method' => 'post')) !!}
        {!! Form::label('isSymmetric', 'Are the session times standard (the same) for all tracks?', array('class' => 'control-label',
        'data-toggle'=>'tooltip', 'title'=>'If most sessions are, say yes for now.  You can change this later (after setting up the similar sessions) to adjust the times of individual differences.')) !!}
        @if($event->isSymmetric !== null && $event->isSymmetric != 1)
            <div class="col-sm-4"> {!! Form::label('isSymmetric', 'No', array('class' => 'control-label')) !!} </div>
            <div class="col-sm-4">{!! Form::checkbox('isSymmetric', '1', false, array('class' => 'flat js-switch', 'onchange' => 'javascript:submit()')) !!}</div>
            <div class="col-sm-4">{!! Form::label('isSymmetric', 'Yes', array('class' => 'control-label')) !!}</div>
        @else
            <div class="col-sm-4"> {!! Form::label('isSymmetric', 'No', array('class' => 'control-label')) !!} </div>
            <div class="col-sm-4">{!! Form::checkbox('isSymmetric', '1', true, array('class' => 'flat js-switch', 'onchange' => 'javascript:submit();')) !!}</div>
            <div class="col-sm-4">{!! Form::label('isSymmetric', 'Yes', array('class' => 'control-label')) !!}</div>
        @endif
        {!! Form::close() !!}
    </div>
    <div class="col-sm-3">
        {!! Form::label('confDays', 'How many days of sessions require planning for this event?', array('class' => 'control-label')) !!}
        <div class="col-sm-12 col-md-12 col-xs-12">
            <b><a style="color:red;" id="confDays" data-pk="{{ $event->eventID }}"
                  data-url="/eventDays/{{ $event->eventID }}" data-value="{{ $event->confDays }}"></a></b>
        </div>
    </div>

    @include('v1.parts.end_content')

    @if($event->confDays != 0)
        <div class="col-sm-12 col-md-12 col-xs-12">

            <table class="table table-bordered table-striped table-condensed table-responsive">
                <thead>
                <tr>
                    @foreach($tracks as $track)
                        @if($tracks->first() == $track || !$event->isSymmetric)
                            <th style="text-align:left;">Session Times</th>
                        @endif
                        <th colspan="2" style="text-align:center;">
                            <a id="trackName{{ $track->trackID }}"
                               data-pk="{{ $track->trackID }}"
                               data-url="/track/{{ $track->trackID }}"
                               data-value="{{ $track->trackName }}"></a>
                        </th>
                    @endforeach
                </tr>
                </thead>
                <tbody>

                @for($i=1;$i<=$event->confDays;$i++)
                    <tr>
<?php
                        $x = EventSession::where([
                            ['confDay', '=', $i],
                            ['eventID', '=', $event->eventID]
                        ])->first();
?>
                        <th style="text-align:center; color: white; background-color: #2a3f54;" colspan="{{ $columns }}">Day {{ $i }} Sessions using Ticket:
                            <a style="color:yellow;" id="ticketLabel-{{ $i}}"
                               data-pk="{{ $track->trackID }}"
                               data-url="/trackticket/{{ $i }}"
                               data-value="{{ $x->ticketID  }}"></a>
                        </th>
                    </tr>

                    @for($x=1;$x<=5;$x++)
                        <tr>
                            @foreach($tracks as $track)
<?php
                                $s = EventSession::where([
                                    ['trackID', $track->trackID],
                                    ['eventID', $event->eventID],
                                    ['confDay', $i],
                                    ['order', $x]
                                ])->first();
?>
                                @if($s !== null)
                                    @if($tracks->first() == $track || !$event->isSymmetric)
                                        <td rowspan="4" style="text-align:left;">
                                            <nobr>
                                                <a id="start-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"
                                                   data-url="/eventsession/{{ $s->eventID }}"
                                                   data-pk="{{ $s->sessionID }}" data-value="{{ $s->start }}"></a>
                                            </nobr>
                                            &dash;
                                            <nobr>
                                                <a id="end-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"
                                                   data-url="/eventsession/{{ $s->eventID }}"
                                                   data-pk="{{ $s->sessionID }}" data-value="{{ $s->end }}"></a></nobr>
                                            <br/>
                                            @if($s->sessionName === null)
                                                {!! Form::open(array('url' => "/session/".$s->sessionID, 'method' => 'delete')) !!}
                                                <button type="submit" class="btn btn-danger btn-xs"><i
                                                            class="fa fa-trash"></i></button>
                                                {!! Form::close() !!}
                                            @endif
                                        </td>
                                    @endif
                                    <td colspan="2" style="text-align:left; min-width:150px; width: {{ $width }}%; max-width: {{ $mw }}%;">
                                        <label for="sessionName-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"
                                               style="color: #2a3f54;" class="control-label">Session Title</label><br/>
                                        <a id="sessionName-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"
                                           data-pk="{{ $s->sessionID }}"
                                           data-url="/eventsession/{{ $event->eventID }}"
                                           data-value="{{ $s->sessionName }}"></a>
                                    </td>
                                @else
                                @endif
                            @endforeach
                        </tr>

                        <tr>
                                @foreach($tracks as $track)
<?php
                                    $s = EventSession::where([
                                        ['trackID', $track->trackID],
                                        ['eventID', $event->eventID],
                                        ['confDay', $i],
                                        ['order', $x]
                                    ])->first();
?>
    @if($s !== null)
                                    <td colspan="2" style="text-align:left;">
                                        <label for="sessionSpeakers-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"
                                               style="color: #2a3f54;" class="control-label">Session Speaker(s)</label><br/>
                                        <a id="sessionSpeakers-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"
                                           data-pk="{{ $s->sessionID }}"
                                           data-url="/eventsession/{{ $event->eventID }}"
                                           data-value="{{ $s->sessionSpeakers }}"></a>
                                    </td>
    @endif
                                @endforeach
                            </tr>

                            <tr>
                                @foreach($tracks as $track)
<?php
                                    $s = EventSession::where([
                                        ['trackID', $track->trackID],
                                        ['eventID', $event->eventID],
                                        ['confDay', $i],
                                        ['order', $x]
                                    ])->first();
?>
    @if($s !== null)
                                    <td style="text-align:left;">
                                        <label for="creditArea-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"
                                               style="color: #2a3f54;" class="control-label">{{ $s->creditAmt }}</label>
                                        <a id="creditArea-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"
                                           data-pk="{{ $s->sessionID }}"
                                           data-url="/eventsession/{{ $event->eventID }}"
                                           data-value="{{ $s->creditArea }}"></a>
                                        <label style="color: #2a3f54;" for="creditArea-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}">
                                            {{ $s->event->org->creditLabel }}<?php if($s->creditAmt > 1) {
                                                echo('s');
                                            } ?>
                                        </label>
                                    </td>
                                    <td style="text-align:left;">
                                        <label style="color: #2a3f54;" for="maxAttendees-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}">
                                        Attendee Limit: </label>
                                        <a id="maxAttendees-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"
                                           data-pk="{{ $s->sessionID }}"
                                           data-url="/eventsession/{{ $event->eventID }}"
                                           data-value="{{ $s->maxAttendees }}"></a>
                                    </td>
    @endif
                                @endforeach
                            </tr>

                            <tr>
                                @foreach($tracks as $track)
<?php
                                    $s = EventSession::where([
                                        ['trackID', $track->trackID],
                                        ['eventID', $event->eventID],
                                        ['confDay', $i],
                                        ['order', $x]
                                    ])->first();
?>
    @if($s !== null)
                                    <td colspan="2" style="text-align:left;">
                                        <label for="sessionAbstract-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"
                                               style="color: #2a3f54;" class="control-label">Abstract</label><br/>
                                        <a id="sessionAbstract-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"
                                           data-pk="{{ $s->sessionID }}"
                                           data-url="/eventsession/{{ $event->eventID }}"
                                           data-value="{{ $s->sessionAbstract }}"></a>
                                    </td>
    @endif
                                @endforeach
                            </tr>
                    @endfor
                @endfor

                </tbody>
            </table>
        </div>
    @endif

    @include('v1.parts.end_content')

@endsection


@section('scripts')
    <!-- script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/inputs-ext/wysihtml5/bootstrap-wysihtml5-0.0.2/bootstrap-wysihtml5-0.0.2.css"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/wysihtml5/0.3.0/wysihtml5.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap3-wysiwyg/0.3.3/bootstrap3-wysihtml5.all.min.js"></script -->
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.fn.editable.defaults.mode = 'inline';
        });
    </script>
    <script>
        $(document).ready(function () {
            @foreach($tracks as $track)
            $("#trackName{{ $track->trackID }}").editable({type: 'text'});
            @endforeach

            $("#confDays").editable({
                type: 'select',
                source: [
                    {value: '0', text: '0'}, {value: '1', text: '1'}, {value: '2', text: '2'},
                    {value: '3', text: '3'}, {value: '4', text: '4'}, {value: '5', text: '5'},
                    {value: '6', text: '6'}, {value: '7', text: '7'}
                ],
                success: function () {
                    window.location = '{{ env('APP_URL') . "/tracks/" . $event->eventID }}';
                }
            });

            @for($i=1;$i<=$event->confDays;$i++)

            $("#ticketLabel-{{ $i }}").editable({
                type: 'select',
                source: [
                        @foreach($tickets as $t)
                    {
                        value: '{{ $t->ticketID }}', text: '{{ $t->ticketLabel }}'
                    }
                    @if($tickets->last() != $t)
                    ,
                    @endif
                    @endforeach
                ]
            });

            @endfor

<?php
            $sessions = EventSession::where('eventID', $event->eventID)->orderBy('trackID', 'order')->get()
?>
            @foreach($sessions as $s)
            @if($s->deleted_at === null)

            $("#start-{{ $s->trackID . "-" . $s->confDay . "-" . $s->order }}").editable({
                type: 'combodate',
                template: 'MMM DD YYYY h:mm A',
                format: 'YYYY-MM-DD HH:mm:ss',
                viewformat: 'h:mm A',
                combodate: {
                    minYear: '{{ date("Y") }}',
                    maxYear: '{{ date("Y")+3 }}',
                    minuteStep: 15
                }
            });
            $("#end-{{ $s->trackID . "-" . $s->confDay . "-" . $s->order }}").editable({
                type: 'combodate',
                template: 'MMM DD YYYY h:mm A',
                format: 'YYYY-MM-DD HH:mm:ss',
                viewformat: 'h:mm A',
                combodate: {
                    minYear: '{{ date("Y") }}',
                    maxYear: '{{ date("Y")+3 }}',
                    minuteStep: 15
                },
                success: function () {
                    window.location = '{{ env('APP_URL') . "/tracks/" . $event->eventID }}';
                }
            });
            $("#sessionName-{{ $s->trackID . "-" . $s->confDay . "-" . $s->order }}").editable({
                type: 'text',
                success: function (data) {
                    console.log(data);
                }
            });

            $("#maxAttendees-{{ $s->trackID . "-" . $s->confDay . "-" . $s->order }}").editable({type: 'text'});

            $("#sessionSpeakers-{{ $s->trackID . "-" . $s->confDay . "-" . $s->order }}").editable({
                type: 'text',
                success: function (data) {
                    //window.location = '{{ env('APP_URL') . "/tracks/" . $event->eventID }}';
                    console.log(data);
                }
            });

            $("#creditArea-{{ $s->trackID . "-" . $s->confDay . "-" . $s->order }}").editable({
                type: 'select',
                source: [
                    {value: 'Leadership', text: 'Leadership'},
                    {value: 'Strategy', text: 'Strategy'},
                    {value: 'Technical', text: 'Technical'}
                ],
                success: function (data) {
                    console.log(data);
                    // window.location = '{{ env('APP_URL') . "/tracks/" . $event->eventID }}';
                },
                error: function (data, exception) {
                    console.log(data);
                    console.log(exception);
                }
            });
            $("#sessionAbstract-{{ $s->trackID . "-" . $s->confDay . "-" . $s->order }}").editable({type: 'textarea'});

            @endif
            @endforeach
        });
    </script>
    <script>
        $(document).ready(function () {
            var setContentHeight = function () {
                // reset height
                $RIGHT_COL.css('min-height', $(window).height());

                var bodyHeight = $BODY.outerHeight(),
                    footerHeight = $BODY.hasClass('footer_fixed') ? -10 : $FOOTER.height(),
                    leftColHeight = $LEFT_COL.eq(1).height() + $SIDEBAR_FOOTER.height(),
                    contentHeight = bodyHeight < leftColHeight ? leftColHeight : bodyHeight;

                // normalize content
                contentHeight -= $NAV_MENU.height() + footerHeight;

                $RIGHT_COL.css('min-height', contentHeight);
            };

            $SIDEBAR_MENU.find('a[href="/event/create"]').parent('li').addClass('current-page').parents('ul').slideDown(function () {
                setContentHeight();
            }).parent().addClass('active');

            $("#add").text('Track & Session Setup');
        });
    </script>
@endsection

@section('modal')
@endsection
<?php
/**
 * Comment: Track and Session Setup for PD Day Events
 * Created: 4/11/2017
 */
use App\EventSession;
use App\Ticket;

$topBits = '';

if ($event->isSymmetric) {
    $columns = ($event->hasTracks * 2) + 1;
    $width = (integer)85 / $event->hasTracks;
    $mw = (integer)90 / $event->hasTracks;
} else {
    $columns = $event->hasTracks * 3;
    $width = (integer)80 / $event->hasTracks;
    $mw = (integer)85 / $event->hasTracks;
}

$tickets = Ticket::where([
    ['eventID', $event->eventID],
    ['isaBundle', '=', 0]
])->get();

?>

@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    <div class="col-xs-12">
        <div class="col-xs-6">
            @include('v1.parts.event_buttons', ['event' => $event])
        </div>
    </div>

    @include('v1.parts.start_content', ['header' => trans('messages.headers.t&ss'). ': ' . $event->eventName,
             'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    @include('v1.parts.start_content', ['header' => trans('messages.headers.sess_setup'), 'subheader' => '',
             'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
    <div class="col-sm-6">
        <ol>
            {!! trans('messages.instructions.track_setup') !!}
        </ol>
    </div>
    <div class="col-sm-3">
        {!! Form::open(array('url' => env('APP_URL').'/tracksymmetry/'.$event->eventID, 'method' => 'post')) !!}
        <label for="isSymmetric" class="control-label">@lang('messages.headers.std_times')
            @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.std_times')])
        </label>
        @if($event->isSymmetric !== null && $event->isSymmetric != 1)
            <div class="col-sm-4"> {!! Form::label('isSymmetric', trans('messages.yesno_check.no'), array('class' => 'control-label')) !!} </div>
            <div class="col-sm-4">{!! Form::checkbox('isSymmetric', '1', false, array('class' => 'js-switch', 'onchange' => 'javascript:submit()')) !!}</div>
            <div class="col-sm-4">{!! Form::label('isSymmetric', trans('messages.yesno_check.yes'), array('class' => 'control-label')) !!}</div>
        @else
            <div class="col-sm-4"> {!! Form::label('isSymmetric', trans('messages.yesno_check.no'), array('class' => 'control-label')) !!} </div>
            <div class="col-sm-4">{!! Form::checkbox('isSymmetric', '1', true, array('class' => 'js-switch', 'onchange' => 'javascript:submit();')) !!}</div>
            <div class="col-sm-4">{!! Form::label('isSymmetric', trans('messages.yesno_check.yes'), array('class' => 'control-label')) !!}</div>
        @endif
        {!! Form::close() !!}
    </div>
    <div class="col-sm-3">
        {!! Form::label('confDays', trans('messages.headers.confDays'), array('class' => 'control-label')) !!}
        <div class="col-sm-12 col-md-12 col-xs-12">
            <b><a style="color:red;" id="confDays" data-pk="{{ $event->eventID }}"
                  data-url="{{ env('APP_URL') }}/eventDays/{{ $event->eventID }}"
                  data-value="{{ $event->confDays }}"></a></b>
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
                            <th style="text-align:left;">@lang('messages.fields.sess_times')</th>
                        @endif
                        <th colspan="2" style="text-align:center;">
                            <a id="trackName{{ $track->trackID }}"
                               data-pk="{{ $track->trackID }}"
                               data-url="{{ env('APP_URL') }}/track/{{ $track->trackID }}"
                               data-value="{{ $track->trackName }}"></a>
                        </th>
                    @endforeach
                </tr>
                </thead>
                <tbody>

                {{-- For each day of the conference... --}}
                @for($i=1;$i<=$event->confDays;$i++)
                    <tr>
<?php
                        // Getting any session from that day to grab the Ticket Name
                        $x = EventSession::where([
                            ['confDay', '=', $i],
                            ['eventID', '=', $event->eventID]
                        ])->first();
?>
                        <th style="text-align:center; color: white; background-color: #2a3f54;"
                            colspan="{{ $columns }}">@lang('messages.headers.day') {{ $i }} @lang('messages.headers.sess_tkt')
                            :
                            <a style="color:yellow;" id="ticketLabel-{{ $i}}"
                               data-pk="{{ $track->trackID }}"
                               data-url="{{ env('APP_URL') }}/trackticket/{{ $i }}"
                               data-value="{{ $x->ticketID  }}"></a>
                        </th>
                    </tr>

                    {{-- For each of the potential sessions (in a track)... --}}
                    @for($x=1;$x<=5;$x++)
<?php
                        // Check to see if there are any events for $x (this row)
                        $check = EventSession::where([
                            ['eventID', $event->eventID],
                            ['confDay', $i],
                            ['order', $x]
                        ])->first();

                        // As long as there are any sessions, if this is the first track
                        // or if non symmetric, show the times
?>
                        @if($check !== null)
                            <tr>
                                @foreach($tracks as $track)
                                    {{-- For each of the tracks... --}}
<?php
                                    $s = EventSession::where([
                                        ['trackID', $track->trackID],
                                        ['eventID', $event->eventID],
                                        ['confDay', $i],
                                        ['order', $x]
                                    ])->first();

                                    // If this particular session exists...
                                    // ...give the option to delete it from DB if  the sessionName is null
?>
                                    @if($tracks->first() == $track || !$event->isSymmetric)
                                        <td rowspan="4" style="text-align:left;">
                                            @if($s !== null)
                                                <nobr>
                                                    <a id="start-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"
                                                       data-url="{{ env('APP_URL') }}/eventsession/{{ $s->eventID }}"
                                                       data-pk="{{ $s->sessionID }}" data-value="{{ $s->start }}"></a>
                                                </nobr>
                                                &dash;
                                                <nobr>
                                                    <a id="end-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"
                                                       data-url="{{ env('APP_URL') }}/eventsession/{{ $s->eventID }}"
                                                       data-pk="{{ $s->sessionID }}" data-value="{{ $s->end }}"></a>
                                                </nobr>
                                                <br/>
                                                @if($s !== null)
                                                    @if($s->sessionName === null)
                                                        {!! Form::open(array('url' => env('APP_URL')."/session/".$s->sessionID, 'method' => 'delete')) !!}
                                                        <button type="submit" class="btn btn-danger btn-xs"><i
                                                                    class="fa fa-trash"></i></button>
                                                        {!! Form::close() !!}
                                                    @endif
                                                @endif
                                            @else
                                                &nbsp;
                                            @endif
                                        </td>
                                    @endif
                                    <td colspan="2"
                                        style="text-align:left; min-width:150px; width: {{ $width }}%; max-width: {{ $mw }}%;">
                                        @if($s !== null)
                                            <label for="sessionName-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"
                                                   style="color: #2a3f54;"
                                                   class="control-label">@lang('messages.headers.sess_title')</label>
                                            <small>({{ $s->sessionID }})</small><br/>
                                            <a id="sessionName-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"
                                               data-pk="{{ $s->sessionID }}"
                                               data-url="{{ env('APP_URL') }}/eventsession/{{ $event->eventID }}"
                                               data-value="{{ $s->sessionName }}"></a>
                                        @else
                                            &nbsp;
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @else
                            &nbsp;
                        @endif
                        {{-- @endfor --}}

<?php
                        // Check to see if there are any events for $x (this row)
                        $check = EventSession::where([
                            ['eventID', $event->eventID],
                            ['confDay', $i],
                            ['order', $x]
                        ])->first();

                        // As long as there are any sessions, the row will be displayed
?>
                        @if($check !== null)
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
                                    <td colspan="2" style="text-align:left;">
                                        @if($s !== null)
                                            <label for="sessionSpeakers-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"
                                                   style="color: #2a3f54;"
                                                   class="control-label">@lang('messages.headers.sess_spk')</label>
                                            @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.speaker_choices')])
                                            <br/>
                                            <a id="sessionSpeakers-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"
                                               data-pk="{{ $s->sessionID }}"
                                               data-url="{{ env('APP_URL') }}/eventsession/{{ $event->eventID }}"></a>
                                        @else
                                            &nbsp;
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endif

<?php
                        // Check to see if there are any events for $x (this row)
                        $check = EventSession::where([
                            ['eventID', $event->eventID],
                            ['confDay', $i],
                            ['order', $x]
                        ])->first();

                        // As long as there are any sessions, the row will be displayed
?>
                        @if($check !== null)
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
                                            {{--
                                            <a id="creditArea-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"
                                               data-pk="{{ $s->sessionID }}"
                                               data-url="{{ env('APP_URL') }}/eventsession/{{ $event->eventID }}"
                                               data-value="{{ $s->creditArea }}"></a>
                                            --}}
                                            <a id="leadAmt-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"
                                               data-pk="{{ $s->sessionID }}"
                                               data-url="{{ env('APP_URL') }}/eventsession/{{ $event->eventID }}"
                                               data-value="{{ $s->leadAmt }}"></a>
                                            <label style="color: #2a3f54;"
                                                   for="leadAmt-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"> @lang('messages.pdus.lead')
                                                {{ $s->event->org->creditLabel }}<?php
                                                if ($s->leadAmt != 1) {
                                                    echo('s');
                                                }
?>
                                            </label><br/>
                                            <a id="stratAmt-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"
                                               data-pk="{{ $s->sessionID }}"
                                               data-url="{{ env('APP_URL') }}/eventsession/{{ $event->eventID }}"
                                               data-value="{{ $s->stratAmt }}"></a>
                                            <label style="color: #2a3f54;"
                                                   for="stratAmt-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"> @lang('messages.pdus.strat')
                                                {{ $s->event->org->creditLabel }}<?php
                                                if ($s->stratAmt != 1) {
                                                    echo('s');
                                                }
?>
                                            </label><br/>
                                            <a id="techAmt-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"
                                               data-pk="{{ $s->sessionID }}"
                                               data-url="{{ env('APP_URL') }}/eventsession/{{ $event->eventID }}"
                                               data-value="{{ $s->techAmt }}"></a>
                                            <label style="color: #2a3f54;"
                                                   for="techAmt-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"> @lang('messages.pdus.tech')
                                                {{ $s->event->org->creditLabel }}<?php
                                                if ($s->techAmt != 1) {
                                                    echo('s');
                                                }
?>
                                            </label><br/>
                                            <label for="creditArea-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"
                                                   style="color: #2a3f54;" class="control-label">
                                                {{ $s->creditAmt }} @lang('messages.fields.total') {{ $s->event->org->creditLabel }}<?php
                                                if ($s->creditAmt != 1) {
                                                    echo('s');
                                                }
?>
                                            </label>
                                        </td>
                                        <td style="text-align:left;">
                                            <label style="color: #2a3f54;"
                                                   for="maxAttendees-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}">
                                                @lang('messages.headers.att_limit'): </label>
                                            <a id="maxAttendees-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"
                                               data-pk="{{ $s->sessionID }}"
                                               data-url="{{ env('APP_URL') }}/eventsession/{{ $event->eventID }}"
                                               data-value="{{ $s->maxAttendees }}"></a>
                                        </td>
                                    @else
                                        <td colspan="2"> &nbsp;</td>
                                    @endif
                                @endforeach
                            </tr>
                        @endif

<?php
                        // Check to see if there are any events for $x (this row)
                        $check = EventSession::where([
                            ['eventID', $event->eventID],
                            ['confDay', $i],
                            ['order', $x]
                        ])->first();

                        // As long as there are any sessions, the row will be displayed
?>
                        @if($check !== null)
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
                                    <td colspan="2" style="text-align:left;">
                                        @if($s !== null)
                                            <label for="sessionAbstract-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"
                                                   style="color: #2a3f54;"
                                                   class="control-label">@lang('messages.fields.abstract')</label><br/>
                                            <a id="sessionAbstract-{{ $track->trackID . "-" . $s->confDay . "-" . $s->order }}"
                                               data-pk="{{ $s->sessionID }}"
                                               data-url="{{ env('APP_URL') }}/eventsession/{{ $event->eventID }}"
                                               data-value="{{ $s->sessionAbstract }}"></a>
                                        @else
                                            &nbsp;
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endif

                    @endfor
                    {{-- this closes the x=1to5 loop --}}
                @endfor
                {{-- this closes the confDays loop --}}

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
            $.fn.editable.defaults.mode = 'popup';
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
            $sessions = EventSession::where([
                ['eventID', $event->eventID],
                ['sessionID', '!=', 0]
            ])->orderBy('trackID', 'order')->get();
?>
            @foreach($sessions as $s)
            @if($s->deleted_at === null)

            $("#start-{{ $s->trackID . "-" . $s->confDay . "-" . $s->order }}").editable({
                type: 'combodate',
                template: 'MMM DD YYYY h:mm A',
                format: 'YYYY-MM-DD HH:mm:ss',
                placement: 'right',
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
                placement: 'right',
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
                type: 'checklist',
                @if(count($s->speakers) > 0)

                value: [
                    @foreach($s->speakers as $p)
                    '{{ $p->id }}'
                    @if($s->speakers->last() != $p)
                    ,
                    @endif
                    @endforeach
                ],
                @endif
                source: [
                    @foreach($spk_list as $sp)
                        { value: '{!! $sp->personID !!}', text: '{!! $sp->showFullName() !!}' }@if($spk_list->last() != $sp),
                    @endif
                    @endforeach
                ],
                success: function (data) {
                    //window.location = '{{ env('APP_URL') . "/tracks/" . $event->eventID }}';
                    console.log(data);
                },
            });

            $("#leadAmt-{{ $s->trackID . "-" . $s->confDay . "-" . $s->order }}").editable({type: 'text'});
            $("#stratAmt-{{ $s->trackID . "-" . $s->confDay . "-" . $s->order }}").editable({type: 'text'});
            $("#techAmt-{{ $s->trackID . "-" . $s->confDay . "-" . $s->order }}").editable({type: 'text'});

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
        })
        ;
    </script>
    @include('v1.parts.menu-fix', array('path' => '/event/create', 'tag' => '#add', 'newTxt' => 'Track & Session Setup'))
@endsection

@section('modal')
@endsection

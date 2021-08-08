<?php
/**
 * Comment: Show the list of possible registrants for a given session
 * Created: 9/17/2019
 *
 * @param: $es
 */

$event = \App\Models\Event::find($es->eventID);
$registrants = $event->registrants($es->sessionID);
?>

@if(null !== $es)

    <h2>{{ $es->sessionName }}</h2>
    {!! Form::open(array('url' => "/$url/$event->eventID", 'method' => 'post', 'id' => "form_".$es->sessionID)) !!}
    {!! Form::hidden('sessionID', $es->sessionID) !!}
    {!! Form::hidden('eventID', $event->eventID) !!}
    <div class="col-xs-12">
        <div class="col-xs-1" style="text-align: right;">
            <b>#</b>
        </div>
        <div id="tool" class="col-xs-1" style="text-align: right;">
            @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.select_all')])
            {!! Form::checkbox('', 1, 0, array('id' => 'select_all', 'class' => 'select_all')) !!}
        </div>
        <div class="col-xs-4">
            <b>{{ trans_choice('messages.headers.att', 1) }}</b>
        </div>
        <div class="col-xs-6">
            <b>@lang('messages.fields.pmi_id')</b>
        </div>
    </div>

    @foreach($registrants as $key => $row)

        @if(null !== $row)
            <div class="col-xs-12" style="text-align: right;{{ $key+1 & 1 ? "background-color: #cccccc;" : ''}}">
                <div class="col-xs-1" style="text-align: right;">
                    @if(null !== $row->hasAttended && $row->hasAttended == 0)
                        <b>{{ $key+1 }}</b>
                    @else
                        {{ $key+1 }}
                    @endif
                </div>
                <div class="col-xs-1" style="text-align: right;">
                    <?php
                    $checked = '';
                    if (null !== $row->regsessions) {
                        if ($row->hasAttended == 1) {
                            $checked = 'checked';
                        }
                    }
                    ?>
                    {!! Form::checkbox('p-'.$row->personID.'-'.$row->regID, 1, 0, array('class' => 'allcheckbox', $checked => $checked)) !!}
                </div>
                <div class="col-xs-4" style="height: 100%; text-align: left;">
                    @if(null !== $row->hasAttended && $row->hasAttended == 0)
                        <b> {{ $row->prefName }} {{ $row->lastName }} </b>
                    @else
                        {{ $row->prefName }} {{ $row->lastName }}
                    @endif
                </div>
                <div class="col-xs-6" style="height: 100%; text-align: left;">
                    @if(null !== $row->hasAttended && $row->hasAttended == 0)
                        <b> {{ $row->OrgStat1 ?? 'N/A' }} </b>
                    @else
                        {{ $row->OrgStat1 ?? 'N/A' }}
                    @endif
                </div>
            </div>
        @endif

    @endforeach
    <div class="col-xs-10 col-xs-offset-2 form-group">
        {!! Form::submit(trans('messages.buttons.chk_att'), ["class" => "btn btn-success btn-sm", 'name' => 'chk_att']) !!}
        {{-- Form::submit(trans('messages.buttons.chk_walk'), ["class" => "btn btn-primary btn-sm", 'name' => 'chk_walk']) --}}
    </div>
    {!! Form::close() !!}
@endif
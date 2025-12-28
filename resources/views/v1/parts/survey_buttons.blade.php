<?php
/**
 * Comment: Likart Choices for Survey
 * Created: 2/12/2019
 *
 * @param button_name
 */
?>
<div class="form-group col-xs-12">
    <div style="text-align:center;" class="form-group col-xs-2">
        {{ html()->radio($button_name, false, '4')->attributes($attributes = array('class'=>'form-control', 'required')) }}
    </div>

    <div style="text-align:center;" class="form-group col-xs-2">
        {{ html()->radio($button_name, false, '3')->attributes($attributes = array('class'=>'form-control', 'required')) }}
    </div>

    <div style="text-align:center;" class="form-group col-xs-1"></div>
    <div style="text-align:center;" class="form-group col-xs-2">
        {{ html()->radio($button_name, false, '2')->attributes($attributes = array('class'=>'form-control', 'required')) }}
    </div>

    <div style="text-align:center;" class="form-group col-xs-1"></div>
    <div style="text-align:center;" class="form-group col-xs-2">
        {{ html()->radio($button_name, false, '1')->attributes($attributes = array('class'=>'form-control', 'required')) }}
    </div>

    <div style="text-align:center;" class="form-group col-xs-2">
        {{ html()->radio($button_name, false, '0')->attributes($attributes = array('class'=>'form-control', 'required')) }}
    </div>
</div>
<div style="text-align:center;" class="form-group col-md-12 col-xs-12">
    <div class="form-group col-xs-2">
        {{ html()->label(trans('messages.surveys.responses.vg'), $button_name)->class('control-label') }}
    </div>

    <div style="text-align:center;" class="form-group col-xs-2">
        {{ html()->label(trans('messages.surveys.responses.g'), $button_name)->class('control-label') }}
    </div>

    <div style="text-align:center;" class="form-group col-xs-3">
        {{ html()->label(trans('messages.surveys.responses.ni'), $button_name)->class('control-label') }}
    </div>

    <div style="text-align:center;" class="form-group col-xs-3">
        {{ html()->label(trans('messages.surveys.responses.dne'), $button_name)->class('control-label') }}
    </div>

    <div style="text-align:center;" class="form-group col-xs-2">
        {{ html()->label(trans('messages.surveys.responses.nc'), $button_name)->class('control-label') }}
    </div>
</div>

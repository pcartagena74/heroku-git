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
        {!! Form::radio($button_name, '4', false, $attributes = array('class'=>'form-control', 'required')) !!}
    </div>

    <div style="text-align:center;" class="form-group col-xs-2">
        {!! Form::radio($button_name, '3', false, $attributes = array('class'=>'form-control', 'required')) !!}
    </div>

    <div style="text-align:center;" class="form-group col-xs-2">
        {!! Form::radio($button_name, '2', false, $attributes = array('class'=>'form-control', 'required')) !!}
    </div>
    <div style="text-align:center;" class="form-group col-xs-1"></div>

    <div style="text-align:center;" class="form-group col-xs-2">
        {!! Form::radio($button_name, '1', false, $attributes = array('class'=>'form-control', 'required')) !!}
    </div>
    <div style="text-align:center;" class="form-group col-xs-1"></div>

    <div style="text-align:center;" class="form-group col-xs-2">
        {!! Form::radio($button_name, '0', false, $attributes = array('class'=>'form-control', 'required')) !!}
    </div>
</div>
<div style="text-align:center;" class="form-group col-md-12 col-xs-12">
    <div class="form-group col-xs-2">
        {!! Form::label($button_name, trans('messages.surveys.responses.vg'), array('class' => 'control-label')) !!}
    </div>

    <div style="text-align:center;" class="form-group col-xs-2">
        {!! Form::label($button_name, trans('messages.surveys.responses.g'), array('class' => 'control-label')) !!}
    </div>

    <div style="text-align:center;" class="form-group col-xs-3">
        {!! Form::label($button_name, trans('messages.surveys.responses.ni'), array('class' => 'control-label')) !!}
    </div>

    <div style="text-align:center;" class="form-group col-xs-3">
        {!! Form::label($button_name, trans('messages.surveys.responses.dne'), array('class' => 'control-label')) !!}
    </div>

    <div style="text-align:center;" class="form-group col-xs-2">
        {!! Form::label($button_name, trans('messages.surveys.responses.nc'), array('class' => 'control-label')) !!}
    </div>
</div>

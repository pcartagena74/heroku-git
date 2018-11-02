<?php
/**
 * Comment: Added to have a parametrized template for the typeahead field
 * Created: 10/21/2018
 *
 * parameters:
 * @param: $name: the name and javascript objectID of the object
 * @param: $label: the label to be associated with the field -- does not display if not provided
 * @param: $width: will default to 12 if not sent
 *
 */

if(!isset($name)){
    $name = 'querystring';
}
if(!isset($width)){
    $width = '12';
}

?>

<div id="custom-template" class="col-sm-{{ $width }}">
    @if($label)
    {!! Form::label($name, trans('messages.instructions.become_instr'). ":") !!}<br/>
    @endif
    {!! Form::text($name, null, array('id' => $name, 'class' => 'typeahead input-xs')) !!}<br />
    <div id="search-results"></div>
</div>

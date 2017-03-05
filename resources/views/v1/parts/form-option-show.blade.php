<?php
/**
 * Comment: This is a display part to enumerate the options from a table
 * Created: 2/10/2017
 *
 * @param   $array []     an array with 1 (text only) or 2 (value, text) to output accordingly
 *
 */
?>

@foreach($array as $row)
        <option value="{{ $row->value }}">{{ $row->text }}</option>
@endforeach

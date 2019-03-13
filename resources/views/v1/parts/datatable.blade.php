<?php
/**
 * Comment: This is a template for tables that should have scroll-y bits
 *          Third parameter will be sent based on the count to change the ID
 * Created: 2/3/2017
 *
 * @param   $headers    array of text strings for dynamic number of columns
 * @param   $data       array of table data array
 * @param   $scroll     binary: 1 for 'generic_table' or 0 for 'datatable-fixed-header'  overrides
 *
 * Optional override parameters:
 * @param   $id         set a specific ID - good when multiple scrolling tables are needed
 *
 */

if(!isset($id)){
    $scroll == 0 ? $id = 'generic_table' : $id = 'datatable-fixed-header';
}
$th_count = 0; $tb_count = 0;

$width = number_format(100/count($headers), 0, '', '');

?>
<div id="not">
    <table id="{{ $id }}" class="col-sm-12 table compact table-striped table-bordered table-condensed table-responsive cf">
        <thead class="cf">
        <tr>
            @foreach ($headers as $header)
<?php $th_count++; ?>
                @if(isset($hidecol[$th_count]))
                    <th style="display:none;">{!! $header !!}</th>
                @else
                    <th style="vertical-align: top; text-align: left;">{!! $header !!}</th>
                @endif
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach ($data as $row)
<?php $tb_count = 0; ?>
            <tr>
                @foreach ($row as $col)
<?php $tb_count++; ?>
                    @if(isset($hidecol[$tb_count]))
                            <td data-title="{!! $headers[$tb_count-1] !!}" style="display: none;">{!! $col !!}</td>
                    @else
                            <td data-title="{!! $headers[$tb_count-1] !!}" style="vertical-align: top; text-align: left;">{!! $col !!}</td>
                    @endif
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
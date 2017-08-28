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
 */

if(!isset($id)){
    $scroll == 0 ? $id = 'generic_table' : $id = 'datatable-fixed-header';
}
$th_count = 0; $tb_count = 0;
?>
<link href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css">
<div>
    <table id="{{ $id }}" class="table table-striped table-bordered table-condensed table-responsive">
        <thead>
        <tr>
            @foreach ($headers as $header)
                <?php $th_count++; ?>
                @if(isset($hidecol[$th_count]))
                    <th style="display:none;">{{ $header }}</th>
                @else
                    <th style="vertical-align: top; text-align: left; min-width: 1px; max-width: 20%; width: {{ number_format(100/count($headers), 0, '', '') }}%;">{{ $header }}</th>
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
                            <td style="display: none;">{!! $col !!}</td>
                    @else
                            <td style="vertical-align: top; text-align: left;">{!! $col !!}</td>
                    @endif
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
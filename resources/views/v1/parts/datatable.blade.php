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
//<link href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css">
        $width = number_format(100/count($headers), 0, '', '');
//width: {{ $width }}%; min-width: 1px; max-width: 20%;">
?>
<style>
    @media only screen and (max-width: 800px) {

        /* Force table to not be like tables anymore */
        #not table,
        #not thead,
        #not tbody,
        #not th,
        #not td,
        #not tr {
            display: block;
        }

        /* Hide table headers (but not display: none;, for accessibility) */
        #not thead tr {
            position: absolute;
            top: -9999px;
            left: -9999px;
        }

        #not tr { border: 1px solid #ccc; }

        #not td {
            /* Behave  like a "row" */
            border: none;
            border-bottom: 1px solid #eee;
            position: relative;
            padding-left: 50%;
            white-space: normal;
            text-align:left;
        }

        #not td:before {
            /* Now like a table header */
            position: absolute;
            /* Top/left values mimic padding */
            top: 6px;
            left: 6px;
            width: 45%;
            padding-right: 10px;
            white-space: nowrap;
            text-align:left;
            font-weight: bold;
        }

        /*
        Label the data
        */
        #not td:before { content: attr(data-title); }
    }
</style>
<div id="not">
    <table id="{{ $id }}" class="col-sm-12 table compact table-striped table-bordered table-condensed table-responsive cf">
        <thead class="cf">
        <tr>
            @foreach ($headers as $header)
                <?php $th_count++; ?>
                @if(isset($hidecol[$th_count]))
                    <th style="display:none;">{{ $header }}</th>
                @else
                    <th style="vertical-align: top; text-align: left;">
                        {{ $header }}</th>
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
                            <td data-title="{{ $headers[$tb_count-1] }}" style="display: none;">{!! $col !!}</td>
                    @else
                            <td data-title="{{ $headers[$tb_count-1] }}" style="vertical-align: top; text-align: left;">{!! $col !!}</td>
                    @endif
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
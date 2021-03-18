@php
/**
 * Comment: Separating the header css file for inclusion
 * Created: 10/6/2017
 *
 * Updated: 2/18/2019 with style
 */
@endphp
<link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
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
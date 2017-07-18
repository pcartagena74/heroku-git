<?php
/**
 * Comment:
 * Created: 2/7/2017
 */
?>
{{--
<script src="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css"></script>
--}}
<script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/1.10.15/js/dataTables.bootstrap.min.js"></script>
<script src="//cdn.datatables.net/plug-ins/1.10.15/sorting/datetime-moment.js"></script>

<script>

    $(document).ready(function() {
//        $.fn.dataTable.moment( 'MM/DD/YYYY hh:mm a' );
        $('#datatable-fixed-header').DataTable({
            "fixedHeader": true,
            "ordering": true
        });
    });

</script>

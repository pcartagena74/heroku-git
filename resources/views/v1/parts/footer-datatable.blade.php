<?php
/**
 * Comment:
 * Created: 2/7/2017
 */
?>
<script src="//cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/1.10.13/js/dataTables.bootstrap.min.js"></script>

<script>

    $(document).ready(function() {
//        $.fn.dataTable.moment( 'MM/DD/YYYY hh:mm a' );
        $('#datatable-fixed-header').DataTable({
            fixedHeader: true
        });
    });

</script>

<?php
/**
 * Comment:
 * Created: 2/11/2017
 */
?>
<script>
    $(document).ready(function() {
        $('#{{ $fieldname }}').daterangepicker({
            timePicker: {{ $time }},
            autoUpdateInput: true,
            singleDatePicker: {{ $single }},
            showDropdowns: true,
            timePickerIncrement: 15,
            locale: {
                format: 'M/D/Y h:mm A'
            },
        });
    });
</script>

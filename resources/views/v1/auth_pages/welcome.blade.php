@include('v1.parts.header')
    <body>

<?php
        /*
    $hdrs = [
      'eventID', 'orgID', 'eventName', 'eventDesc', 'catID', 'evTid', 'eventInfo', 'evStart', 'evEnd',
        'evTZ', 'locID', 'contactOrg', 'contactEmail', 'contactDetails', 'isActive', 'isPrivate', 'showLogo',
        'img1', 'img2', 'shortURL', 'recur', 'earlyDisc', 'earlyEnd', 'hasBundles', 'refundNote', 'evTags',
        'creatorID', 'createDate', 'updaterID', 'updateDate', 'isDeleted', 'deleteDate'
    ];
        */

$hdrs = [ 'eventID', 'eventName', 'evStart', 'evEnd' ];
?>

@include('v1.parts.start_content', ['header' => '', 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
@include('v1.parts.datatable', ['headers' => $hdrs, 'data' => $events])
@include('v1.parts.end_content')

@include('v1.parts.footer_script')
<script>
    $(document).ready(function() {
        $('#datatable-fixed-header').DataTable({
            fixedHeader: true
        });
    });
</script>
    </body>
</html>

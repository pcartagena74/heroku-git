<?php
/**
 * Comment: Created to facilitate debugging ajax components
 * Created: 10/10/2018
 */
?>

<div style="float: left; width: 50%">
    <h3>Console <small>(all ajax requests here are emulated)</small></h3>
    <div><textarea id="console" class="form-control" rows="8" style="width: 70%" autocomplete="off"></textarea></div>
</div>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>

<!-- <script src="//code.jquery.com/jquery-1.12.4.js"></script> -->
<script src="https://code.jquery.com/jquery-3.1.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-mockjax/1.6.2/jquery.mockjax.min.js"></script>
<script>
    $.mockjax({
        url: "/ticket/*",
        response: function(settings) {
            log(settings, this);
        }
    });

    /*
    $.getJSON("/ticket/*", function(response) {
        if ( response.status == "success") {
            $("#console").html( "Your fortune is: " + response.fortune );
        } else {
            $("#console").html( "Things do not look good, no fortune was told" );
        }
    });
    */
</script>
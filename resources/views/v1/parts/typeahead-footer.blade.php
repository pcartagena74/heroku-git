<?php
/**
 * Comment: a footer template for the typeahead javascript, etc.
 * Created: 10/21/2018
 */
?>

<script src="{{ env('APP_URL') }}/js/typeahead.bundle.min.js"></script>
<script nonce="{{ $cspScriptNonce }}">
    $(document).ready(function ($) {
        var people = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {
                url: '{{ env('APP_URL') }}/autocomplete/?q=%QUERY',
                wildcard: '%QUERY'
            }
        });

        $('#custom-template .typeahead').typeahead(null, {
            name: 'people',
            display: 'value',
            source: people
        });
    });
</script>

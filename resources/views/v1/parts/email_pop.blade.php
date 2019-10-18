<?php
/**
 * Comment: Javascript to insert a passed parameter into an email / login field.
 * Created: 10/17/2019
 */

if (null === $field) {
    $field = 'email';
}
if (null === $parameter) {
    $parameter = 'e';
}
?>
<script>
    $(document).ready(function () {
        function getUrlParams() {

            var paramMap = {};
            if (location.search.length == 0) {
                return paramMap;
            }
            var parts = location.search.substring(1).split("&");

            for (var i = 0; i < parts.length; i ++) {
                var component = parts[i].split("=");
                paramMap [decodeURIComponent(component[0])] = decodeURIComponent(component[1]);
            }
            return paramMap;
        }

        var params = getUrlParams();
        document.getElementById('{{ $field }}').value = params.{{ $parameter }};
    });
</script>
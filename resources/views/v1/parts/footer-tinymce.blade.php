<?php
/**
 * Comment:
 * Created: 2/7/2017
 */
?>
<script src="{!! env('APP_URL') !!}/js/tinymce/tinymce.min.js"></script>
<script>
    tinymce.init({
        mode: "specific_textareas",
        editor_selector: "rich",
        theme: 'modern',
        height: 150,
        plugins: [
            'advlist autolink link image imagetools lists charmap preview hr anchor pagebreak spellchecker',
            'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
            'save table contextmenu directionality emoticons template paste textcolor'
        ],
        toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullpage | backcolor forecolor emoticons | code'
    });

</script>


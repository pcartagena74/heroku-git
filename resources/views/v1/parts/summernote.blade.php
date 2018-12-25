<?php
/**
 * Comment: Footer script and standard setup
 * Created: 4/5/2018
 */
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote.js"> </script>
<script>
    $(document).ready(function(){
        $('.summernote').summernote({
            toolbar: [
                // [groupName, [list of button]]
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear', 'strikethrough', 'superscript', 'subscript']],
                ['font', ['fontname', 'fontsize', 'color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link','picture', 'video']], // image and doc are customized buttons
                ['misc', ['codeview']],
            ]
        });
    });
</script>

<?php
/**
 * Comment: Footer script and standard setup
 * Created: 4/5/2018
 */
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote.js"> </script>
<script src="{!! env('APP_URL') !!}/js/summernote-cleaner.js"></script>
<script>

    $(".summernote").on("summernote.paste",function(e,ne) {

        var bufferText = ((ne.originalEvent || ne).clipboardData || window.clipboardData).getData('Text');
        ne.preventDefault();
        document.execCommand('insertText', false, bufferText);

    });

    $(document).ready(function ($) {
        $('.summernote').summernote({
            toolbar: [
                // [groupName, [list of button]]
                // ['cleaner', ['cleaner']],
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear', 'strikethrough', 'superscript', 'subscript']],
                ['font', ['fontname', 'fontsize', 'color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['table', 'link', 'picture', 'video']], // image and doc are customized buttons
                ['misc', ['codeview']],
            ],

            cleaner:{
                action: 'paste', // both|button|paste 'button' only cleans via toolbar button, 'paste' only clean when pasting content, both does both options.
                // newline: '<br>', // Summernote's default is to use '<p><br></p>'
                notStyle: 'position:absolute;top:0;left:0;right:0', // Position of Notification
                icon: '<i class="note-icon fas fa-broom"></i>',
                keepHtml: true, // Remove all Html formats
                keepOnlyTags: ['<br>', '<ul>', '<li>', '<b>', '<strong>','<i>', '<a>', '<img>'], // If keepHtml is true, remove all tags except these
                keepClasses: false, // Remove Classes
                badTags: ['style', 'script', 'applet', 'embed', 'noframes', 'noscript', 'html'], // Remove full tags with contents
                badAttributes: ['style', 'start', 'MsoNormal'], // Remove attributes from remaining tags
                limitChars: false, // 0/false|# 0/false disables option
                limitDisplay: 'both', // text|html|both
                limitStop: false // true/false
            }
        });
{{--
            onpaste: function (e) {
                var bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');
                e.preventDefault();
                CleanPastedHTML(bufferText);

                setTimeout(function () {
                    document.execCommand('insertText', false, bufferText);
                }, 10);
            }

        function CleanPastedHTML(input) {
            // 1. remove line breaks / Mso classes
            var stringStripper = /(\n|\r| class=(")?Mso[a-zA-Z]+(")?)/g;
            var output = input.replace(stringStripper, ' ');
            // 2. strip Word generated HTML comments
            var commentSripper = new RegExp('<!--(.*?)-->', 'g');
            var output = output.replace(commentSripper, '');
            var tagStripper = new RegExp('<(/)*(meta|p|link|span|\\?xml:|st1:|o:|font)(.*?)>', 'gi');
            // 3. remove tags leave content if any
            output = output.replace(tagStripper, '');
            // 4. Remove everything in between and including tags '<style(.)style(.)>'
            var badTags = ['style', 'script', 'applet', 'embed', 'noframes', 'noscript'];

            for (var i = 0; i < badTags.length; i++) {
                tagStripper = new RegExp('<' + badTags[i] + '.*?' + badTags[i] + '(.*?)>', 'gi');
                output = output.replace(tagStripper, '');
            }
            // 5. remove attributes ' style="..."'
            var badAttributes = ['style', 'start'];
            for (var i = 0; i < badAttributes.length; i++) {
                var attributeStripper = new RegExp(' ' + badAttributes[i] + '="(.*?)"', 'gi');
                output = output.replace(attributeStripper, '');
            }
            return output;
        }
--}}
    });

</script>

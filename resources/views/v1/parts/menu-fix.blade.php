@php
    /**
     * Comment: An includeable script to fix left nav display
     * Created: 11/19/2017
     * @param:
     *       path (the path in the nav)
     *       txtChange: if 1, then there should be 2 more values
     *       tag: the tag to find
     *       newTxt: what should be displayed
     *
     */

    if(!isset($path)) {
        $path = "/event/create";
    }
    if(isset($tag) && isset($newTxt)) {
        $txtChange = 1;
    } else {
        $txtChange = 0;
    }
    $url = url($path);
    if(!empty($url_override)){
        // $url = '{{$url_override}}';
    }
@endphp
<script nonce="{{ $cspScriptNonce }}">
    $(document).ready(function () {
        var setContentHeight = function () {
            // reset height
            $RIGHT_COL.css('min-height', $(window).height());

            var bodyHeight = $BODY.outerHeight(),
                footerHeight = $BODY.hasClass('footer_fixed') ? -10 : $FOOTER.height(),
                leftColHeight = $LEFT_COL.eq(1).height() + $SIDEBAR_FOOTER.height(),
                contentHeight = bodyHeight < leftColHeight ? leftColHeight : bodyHeight;

            // normalize content
            contentHeight -= $NAV_MENU.height() + footerHeight;

            $RIGHT_COL.css('min-height', contentHeight);
        };
        $SIDEBAR_MENU.find('a[href="{{ $url }}"]').parent('li').addClass('current-page').parents('ul').slideDown(function () {
            setContentHeight();
        }).parent().addClass('active');


        @if($txtChange)
        $("{!! $tag !!}").text('{!! $newTxt !!}');
        @endif
    });
</script>

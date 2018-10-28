<?php
/**
 * Comment:  This is a modal intended to load any content
 * Created: 10/22/2017
 *
 * @params:
 * @param $header: a header for the modal
 * @param $body: a variable to hold the content to be displayed
 * @param $show_past: 1 or 0 to show the script below or not
 * @param $content: if passed, it will display below
 *
 */

if(!isset($show_past)){
    $show_past = 0;
}

?>
<div class="modal fade" id="dynamic_modal" tabindex="-1" role="dialog" aria-labelledby="dynamic_label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dynamic_label">{!! $header or trans('messages.modals.default') !!}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div id="dynamic-modal-body" class="modal-body">
                <div class="container">
                    <div class="panel-body" id="modal-content">
                        {{--    Content would go here.    --}}
                        {!! $content or '' !!}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">@lang('messages.buttons.close')</button>
            </div>
        </div>
    </div>
</div>

@if($show_past)
<script>
    $(document).ready(function(){
        $("#dynamic_modal").on("show.bs.modal", function(e){
            var id = $(e.relatedTarget).data('target-id');
            $.get("{!! env('APP_URL') !!}/activity/"+id, function(d){
                var data = JSON.parse(d);
                //console.log(data.html);
                $("#modal-content").html(data.html);
            });
        });
    });
</script>
@endif


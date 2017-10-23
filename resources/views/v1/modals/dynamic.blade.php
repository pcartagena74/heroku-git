<?php
/**
 * Comment:  This is a modal intended to load any content
 * Created: 10/22/2017
 *
 * @params:
 *        $header: a header for the modal
 *        $body: a variable to hold the content to be displayed
 */
?>
<div class="modal fade" id="dynamic_modal" tabindex="-1" role="dialog" aria-labelledby="dynamic_label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dynamic_label">{!! $header or 'Default Title' !!}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div id="dynamic-modal-body" class="modal-body">
                <div class="container">
                    <div class="panel-body">
{{--                    Content goes here.                          --}}

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

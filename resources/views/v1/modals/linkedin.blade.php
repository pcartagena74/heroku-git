<?php
/**
 * Comment:  This is a modal for the LinkedIn Login via OAuth
 * Created: 7/18/2017
 */
?>
<div class="modal fade" id="linkedin_modal" tabindex="-1" role="dialog" aria-labelledby="linkedin_label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="linkedin_label">Connect to Linked In</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div id="linkedin-modal-body" class="modal-body">
                <div class="container">
                    <div class="panel-body">
                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif

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

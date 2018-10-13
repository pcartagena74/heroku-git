<?php
/**
 * Comment: a Confirmation Modal that will submit silently
 * Created: 7/27/2018
 *
 * @param: $id
 * @param: $content
 *
 */

if(!isset($id)){
    $id = 'confirm_modal';
}
if(!isset($content)){
    $content = '';
}
?>

<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" id="{{ $id }}">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">@lang('messages.modals.gConfirm')</h4>
            </div>
            <div id="dynamic-modal-body" class="modal-body">
                <div class="container">
                    <div class="panel-body" id="{{ $id }}-content">
                        {{-- Content will display here --}}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-sm" id="modal-btn-yes">@lang('registration.yesno_check.yes')</button>
                <button type="button" class="btn btn-danger btn-sm" id="modal-btn-no">@lang('registration.yesno_check.no')</button>
            </div>
        </div>
    </div>
</div>

<script>
</script>
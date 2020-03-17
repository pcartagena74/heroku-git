<?php
/**
 * Comment: A popup for context sensitive issue it will append page url in the end of the content
 * Created on: 12/03/2020
 */
?>
<div aria-hidden="true" aria-labelledby="context_issue" class="modal fade" id="context_issue" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {!! trans('ticketit::lang.create-new-ticket') !!}
                </h5>
                <button aria-label="Close" class="close pull-right" data-dismiss="modal" type="button">
                    <span aria-hidden="true">
                        ×
                    </span>
                </button>
            </div>
            {!! CollectiveForm::open([
                        'route'=>'tickets.store',
                        'method' => 'POST',
                        'class' => 'form-horizontal',
                        ]) !!}
            <div class="modal-body">
                <div class="form-group">
                    {!! CollectiveForm::label('subject', trans('ticketit::lang.subject') . trans('ticketit::lang.colon'), ['class' => 'col-lg-2 control-label']) !!}
                    <div class="col-lg-10">
                        {!! CollectiveForm::text('subject', null, ['class' => 'form-control', 'required' => 'required' ,'id'=>'ticket_subject']) !!}
                        <span class="help-block">
                            {!! trans('ticketit::lang.create-ticket-brief-issue') !!}
                        </span>
                    </div>
                </div>
                <div class="form-group">
                    {!! CollectiveForm::label('content', trans('ticketit::lang.description') . trans('ticketit::lang.colon'), ['class' => 'col-lg-2 control-label']) !!}
                    <div class="col-lg-10">
                        {!! CollectiveForm::textarea('content', null, ['class' => 'form-control summernote-editor', 'rows' => '5', 'required' => 'required','id'=>'ticket_content']) !!}
                        <span class="help-block">
                            {!! trans('ticketit::lang.create-ticket-describe-issue') !!}
                        </span>
                    </div>
                </div>
                @if(Entrust::hasRole('Admin') || Entrust::can('Developer'))
                <div class="row">
                    <div class="form-group">
                        {!! CollectiveForm::label('agent_id', trans('ticketit::lang.agent') . trans('ticketit::lang.colon'), [
                            'class' => 'col-lg-4 control-label'
                        ]) !!}
                        <div class="col-lg-8">
                            {!! CollectiveForm::select(
                                'agent_id',
                                getAgentList(),
                                'auto',
                                ['class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
                @endif
                <div class="error" id="ticket-errors">
                </div>
            </div>
            <div class="modal-footer">
                <div class="container">
                    <button class="btn btn-default" data-dismiss="modal" type="button">
                        {{trans('ticketit::lang.btn-close')}}
                    </button>
                    {!! CollectiveForm::button(trans('ticketit::lang.btn-submit'), ['class' => 'btn btn-primary','id'=>'submitTicket']) !!}
                </div>
            </div>
            {!! CollectiveForm::close() !!}
        </div>
    </div>
</div>
<div aria-hidden="true" aria-labelledby="context_issue_success" class="modal fade" id="context_issue_success" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {!! trans('ticketit::lang.create-new-ticket') !!}
                </h5>
                <button aria-label="Close" class="close pull-right" data-dismiss="modal" type="button">
                    <span aria-hidden="true">
                        ×
                    </span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success">
                    {{trans('ticketit::lang.the-ticket-has-been-created')
                                            }}
                </div>
            </div>
            <div class="modal-footer">
                <div class="container">
                    <button class="btn btn-default" data-dismiss="modal" type="button">
                        {{trans('ticketit::lang.btn-close')}}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $('#context_issue').on('show.bs.modal',function(){
        $('#ticket_subject').val('');
        $('#ticket_content').val('');
    });
    $('#submitTicket').click(function(){
        var subject = $('#ticket_subject').val();
        var content = $('#ticket_content').val();
        $('#ticket-errors').html('');
        var url = window.location.href;
        $.ajax({
            url: '{{route("tickets.storeAjax")}}', 
            method:'POST',
            dataType:'json',
            data: { content:content,subject:subject,url:url },
            success: function(result){
                if(result.success == true){
                    $('#context_issue').modal('hide');
                    $('#context_issue_success').find('.modal-body').html(result.message);
                    $('#context_issue_success').modal('show');
                } else {
                    console.log('');
                }
            },
            error(xhr,status,error){
                if(xhr.responseJSON.errors){
                    $.each(xhr.responseJSON.errors,function(key,value){
                        console.log(value[0]);
                        var str = '<div class="alert alert-danger"><a aria-label="close" class="close" data-dismiss="alert" href="#">×</a>'+value[0]+'</div>';
                        $('#ticket-errors').append(str);
                    });
                }
            }
        });
    });
    function submitTickit(event){
        event.preventDefault(); 
        console.log('here');
        return false
    }
</script>

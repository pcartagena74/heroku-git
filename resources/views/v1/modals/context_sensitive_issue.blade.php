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
                @if(App\Models\Ticketit\AgentOver::isAdmin() || App\Models\Ticketit\AgentOver::isAgent())
                <div class="form-inline row">
                    <div class="form-group col-lg-4">
                        {!! CollectiveForm::label('priority', trans('ticketit::lang.priority') . trans('ticketit::lang.colon'), ['class' => 'col-lg-6 control-label']) !!}
                        <div class="col-lg-6">
                            {!! CollectiveForm::select('priority_id', getTicketPriorities(), 2, ['class' => 'form-control', 'required' => 'required','id'=>'ticket_priority']) !!}
                        </div>
                    </div>
                    <div class="form-group col-lg-4">
                        {!! CollectiveForm::label('category', trans('ticketit::lang.category') . trans('ticketit::lang.colon'), ['class' => 'col-lg-6 control-label']) !!}
                        <div class="col-lg-6">
                            {!! CollectiveForm::select('category_id', getTicketCategories(), null, ['class' => 'form-control', 'required' => 'required','id'=>'ticket_category']) !!}
                        </div>
                    </div>
                </div>
                <br>
                    <div class="form-inline row">
                        <div class="form-group col-lg-12">
                            {!! CollectiveForm::label('agent_id', trans('ticketit::lang.agent') . trans('ticketit::lang.colon'), [
                            'class' => 'col-lg-2 control-label'
                        ]) !!}
                            <div class="col-lg-10">
                                {!! CollectiveForm::select(
                                'agent_id',
                                getAgentList(),
                                'auto',
                                ['class' => 'form-control','id'=>'ticket_agent']) !!}
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="error" id="ticket-errors">
                    </div>
                </br>
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
        var url = window.location.href;
        var data = { content:content,subject:subject,url:url };
        @if(Entrust::hasRole('Admin') || Entrust::can('Developer'))
        var priority = $('#ticket_priority').val();
        var category = $('#ticket_category').val();
        var agent = $('#ticket_agent').val();
        data = { content:content,subject:subject,url:url,priority_id:priority,category_id:category,agent_id:agent};
        @endif
        $('#ticket-errors').html('');
        $.ajax({
            url: '{{route("tickets.storeAjax")}}', 
            method:'POST',
            dataType:'json',
            data: data,
            success: function(result){
                if(result.success == true){
                    $('#context_issue').modal('hide');
                    $('#context_issue_success').find('.modal-body').html(result.message);
                    $('#context_issue_success').modal('show');
                } else {
                     $.each(result.errors,function(key,value){
                        var str = '<div class="alert alert-danger"><a aria-label="close" class="close" data-dismiss="alert" href="#">×</a>'+value[0]+'</div>';
                        $('#ticket-errors').append(str);
                    });
                }
            },
            error(xhr,status,error){
                if(xhr.responseJSON.errors){
                    $.each(xhr.responseJSON.errors,function(key,value){
                        
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

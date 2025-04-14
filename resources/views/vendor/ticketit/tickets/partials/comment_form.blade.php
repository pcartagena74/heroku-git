<div class="panel panel-default">
    <div class="panel-body">
        {!!
            html()->form(
                    'POST',
                    $setting->grab('main_route').'-comment.store')
                  ->class('form-horizontal')
                  ->open()
        !!}
        {!! html()->hidden('ticket_id', $ticket->id ) !!}

        <fieldset>
            <legend>{!! trans('ticketit::lang.reply') !!}</legend>
            <div class="form-group">
                <div class="col-lg-12">
                    {{--
                    // commented out due to inadvertent variable interpolation
                        html()->textarea('content', null)
                              ->class(['form-control', 'summernote-editor'])
                              ->rows(3)
                              ->cols(50)
                    --}}
                    <textarea class="form-control summernote-editor" rows="3" name="content" cols="50"></textarea>
                </div>
            </div>

            <div class="text-right col-md-12">
                {!!
                    html()->submit( trans('ticketit::lang.btn-submit'))
                          ->class(['btn btn-primary'])
                !!}
            </div>

        </fieldset>
        {!! html()->form()->close() !!}
    </div>
</div>

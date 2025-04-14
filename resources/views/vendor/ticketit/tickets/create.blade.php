@extends($master)
@section('page', trans('ticketit::lang.create-ticket-title'))

@section('content')
    @include('ticketit::shared.header')
    <div class="well bs-component">
        {{--
        {!! CollectiveForm::open([
                        'route'=>$setting->grab('main_route').'.store',
                        'method' => 'POST',
                        'class' => 'form-horizontal'
                        ]) !!}
        --}}

        {!! html()->form(
                        'POST',
                        $setting->grab('main_route').'.store')
                  ->class(['form-horizontal'])
                  ->open()
        !!}
        <legend>{!! trans('ticketit::lang.create-new-ticket') !!}</legend>
        <div class="form-group">
            {!!
                html()->label(trans('ticketit::lang.subject') . trans('ticketit::lang.colon'), 'subject')
                      ->class(['col-lg-2 control-label'])
            !!}
            <div class="col-lg-10">
                {!!
                    html()->text('subject', null)
                          ->class(['form-control'])
                          ->required()
                !!}
                <span class="help-block">{!! trans('ticketit::lang.create-ticket-brief-issue') !!}</span>
            </div>
        </div>
        <div class="form-group">
            {!!
                html()->label(
                      trans('ticketit::lang.description') . trans('ticketit::lang.colon'),
                      'content')
                      ->class(['col-lg-2 control-label'])
            !!}
            <div class="col-lg-10">
                {!!
                    html()->textarea('content', null)
                    ->class(['form-control','summernote-editor'])
                    ->rows(5)
                    ->required()
                !!}
                <span class="help-block">{!! trans('ticketit::lang.create-ticket-describe-issue') !!}</span>
            </div>
        </div>
        <div class="form-inline row">
            <div class="form-group col-lg-4">
                {!!
                    html()->label(
                            trans('ticketit::lang.priority') . trans('ticketit::lang.colon'),
                            'priority')
                            ->class(['col-lg-6 control-label'])
                !!}
                <div class="col-lg-6">
                    {!!
                        html()->select('priority_id', $priorities, null)
                              ->class(['form-control'])
                              ->required()
                    !!}
                </div>
            </div>
            <div class="form-group col-lg-4">
                {!!
                    html()->label(
                            trans('ticketit::lang.category') . trans('ticketit::lang.colon'),
                            'category')
                            ->class(['col-lg-6 control-label'])
                !!}
                <div class="col-lg-6">
                    {!!
                        html()->select('category_id', $categories, null)
                            ->class(['form-control'])
                            ->required();
                    !!}
                </div>
            </div>
            {!! html()->hidden('agent_id', 'auto') !!}
        </div>
        <br>
        <div class="form-group">
            <div class="col-lg-10 col-lg-offset-2">
                {{ html()->a($setting->grab('main_route') . '.index', trans('ticketit::lang.btn-back'))->class('btn btn-default') }}
                {!!
                    html()->submit(trans('ticketit::lang.btn-submit'))
                          ->class(['btn btn-primary'])
                !!}
            </div>
        </div>
        {!! html()->form()->close() !!}
    </div>
@endsection

@section('footer')
    @include('ticketit::tickets.partials.summernote')
@append
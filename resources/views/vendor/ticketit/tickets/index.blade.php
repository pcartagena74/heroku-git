<div class="panel panel-default">
    <div class="panel-heading">
        <h2>
            {{ trans('ticketit::lang.index-my-tickets') }}
            {{-- @if(App\Models\Ticketit\AgentOver::isAdmin() || App\Models\Ticketit\AgentOver::isAgent())
            {{ html()->a($setting->grab('main_route') . '.create', trans('ticketit::lang.btn-create-new-ticket'))->class('btn btn-primary pull-right') }}
            @else --}}
            <a class="btn btn-primary pull-right" data-target="#context_issue" data-toggle="modal" href="#">
                @lang('ticketit::lang.btn-create-new-ticket')
            </a>
            {{-- @endif --}}
        </h2>
    </div>
    <div class="panel-body">
        <div id="message">
        </div>
        @if(App\Models\Ticketit\AgentOver::isAdmin() || App\Models\Ticketit\AgentOver::isAgent())
            <form>
                <div class="form-group col-sm-6">
                    <div id="search-button-group">
                        <label>
                            {{ trans('messages.fields.filter_tickets') }}
                        </label>
                        <button class="btn btn-primary active" onclick="changeSearch('all',this)" type="button">
                            {{ trans('messages.filter_tickets_options.all') }}
                        </button>
                        <button class="btn btn-primary" onclick="changeSearch('created',this)" type="button">
                            {{ trans('messages.filter_tickets_options.created') }}
                        </button>
                        <button class="btn btn-primary" onclick="changeSearch('assigned',this)" type="button">
                            {{ trans('messages.filter_tickets_options.assigned') }}
                        </button>
                    </div>
                    {{--
                    <select class="form-control input-sm" id="filter_owner">
                        <option value="all">
                            {{ trans('messages.filter_tickets_options.all') }}
                        </option>
                        <option value="created">
                            {{ trans('messages.filter_tickets_options.created') }}
                        </option>
                        <option value="created">
                            {{ trans('messages.filter_tickets_options.assigned') }}
                        </option>
                    </select>
                    --}}
                </div>
            </form>
        @endif
        {{--
        @include('ticketit::tickets.partials.datatable')
        --}}
    </div>
</div>

@section('scripts')
    @include('v1.parts.menu-fix', array('path' => url('tickets')))
@endsection

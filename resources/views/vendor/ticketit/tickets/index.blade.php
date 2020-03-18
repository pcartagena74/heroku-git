<div class="panel panel-default">
    <div class="panel-heading">
        <h2>
            {{ trans('ticketit::lang.index-my-tickets') }}
            {!! link_to_route($setting->grab('main_route').'.create', trans('ticketit::lang.btn-create-new-ticket'), null, ['class' => 'btn btn-primary pull-right']) !!}
        </h2>
    </div>
    <div class="panel-body">
        <div id="message">
        </div>
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
        @include('ticketit::tickets.partials.datatable')
    </div>
</div>

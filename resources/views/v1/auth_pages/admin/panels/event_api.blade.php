@php
    /**
     * Comment:
     * Created: 11/4/2020
     *
     * @var $org_admin_props
     */

$sep = \App\AdminProp::find(1);
$header = \App\AdminProp::find(2);

$sep = $org_admin_props->where('propID', 1)->first();
$header = $org_admin_props->where('propID', 2)->first();
$btn_txt = $org_admin_props->where('propID', 3)->first();
$btn_color = $org_admin_props->where('propID', 4)->first();
$hdr_color = $org_admin_props->where('propID', 5)->first();
$btn_size = $org_admin_props->where('propID', 6)->first();

$colors = [
    'primary' => 'Blue',        // blue
    // 'secondary' => 'Dark Grey', // dark grey
    'success' => 'Green',       // green
    'danger' => 'Red',          // red
    'warning' => 'Yellow',      // yellow
    'info' => 'Teal',           // teal
    'light' => 'White',         // white
    'dark' => 'Black',          // black
    'link' => 'Clear',          // clear
    // 'unique' => 'Maroon',       // maroon
    'pink' => 'Pink',           // pink
    'purple' => 'Purple',       // purple
    'deep-purple' => 'Violet',  // violet
    'deep-orange' => 'Orange',  // orange
    'brown' => 'Brown'          // brown
];

$sizes = [
    'sm' => 'Small',
    'md' => 'Medium',
    'lg' => 'Large',
];

@endphp
<div>
    <template>
    <form @submit.prevent="onSubmit">
        {{-- button params and demo --}}
        <div class="col-sm-8">
            <div class="from-group">
                <div class="col-sm-3">
                    <label class="control-label" for="{{ $btn_txt->prop->name }}">
                        @lang($btn_txt->prop->displayName)
                    </label>
                </div>
                <div class="col-sm-3">
                    <label class="control-label" for="{{ $btn_color->prop->name }}">
                        @lang($btn_color->prop->displayName)
                    </label>
                </div>
                <div class="col-sm-3">
                    <label class="control-label" for="{{ $btn_size->prop->name }}">
                        @lang($btn_size->prop->displayName)
                    </label>
                </div>
                <div class="col-sm-3">
                    <label class="control-label" for="{{ $hdr_color->prop->name }}">
                        @lang($hdr_color->prop->displayName)
                    </label>
                </div>

                <div class="col-sm-3">
                    {!! Form::text($btn_txt->prop->name, $btn_txt->value,
                        ['class' => "form-control input-sm",
                        'id' => $btn_txt->prop->name,
                        'v-model' => 'admin_props[2].value',
                        '@blur' => 'api_update($event.target.name, $event.target.value)']) !!}
                    {{--
                    '@blur' => 'api_update(admin_props[2].value)'])
                    --}}
                </div>
                <div class="col-sm-3">
                    {!! Form::select($btn_color->prop->name, $colors, $btn_color->value,
                        ['class' => "form-control input-sm",
                        'v-model' => 'admin_props[3].value',
                        '@blur' => 'api_update($event.target.name, $event.target.value)']) !!}
                </div>
                <div class="col-sm-3">
                    {!! Form::select($btn_size->prop->name, $sizes, $btn_size->value,
                        ['class' => "form-control input-sm",
                        'v-model' => 'admin_props[5].value',
                        '@blur' => 'api_update($event.target.name, $event.target.value)']) !!}
                </div>
                <div class="col-sm-3">
                    {!! Form::select($hdr_color->prop->name, $colors, $hdr_color->value,
                        ['class' => "form-control input-sm",
                        'v-model' => 'admin_props[4].value',
                        '@blur' => 'api_update($event.target.name, $event.target.value)']) !!}
                </div>
            </div>
        </div>
        <div class="col-sm-4 form-group">
            <button v-bind:class="'btn btn-block ' + 'btn-' + admin_props[5].value + ' btn-' + admin_props[3].value">@{{
                admin_props[2].value }}
            </button>
        </div>

        {{-- header params and demo --}}
        <div class="col-sm-8">
            <div class="from-group">
                <div class="col-sm-3">
                    <label class="control-label" for="{{ $sep->prop->name }}">@lang($sep->prop->displayName)</label>
                </div>
                <div class="col-sm-9">
                    <label class="control-label"
                           for="{{ $header->prop->name }}">@lang($header->prop->displayName)</label>
                </div>
                <div class="col-sm-3">
                    <input class="form-control input-sm" type="text" size="1" name="{{ $sep->prop->name }}"
                           v-model="separator" value="{{ $sep->value }}"
                           @blur="api_update($event.target.name, $event.target.value)">
                </div>
                <div class="col-sm-9">
                    {{--
                    <input class="form-control input-sm" type="text" size="255" name="{{ $header->prop->name }}"
                           v-model="header" value="{{ $header->value }}">
                    --}}
                    <span v-bind:class="'text-'+admin_props[4].value"><b>@{{ admin_props[1].value }}</b></span>
                </div>
            </div>
            <div class="col-xs-12">
                &nbsp;<br/>
                <p>
                    @lang('messages.admin.api.api_instr1')
                </p>
            </div>
            <div class="form-group">
                <div class="col-sm-2" style="text-align: center">
                    <label class="control-label">@lang('messages.fields.category')</label>
                    @include('v1.parts.tooltip', ['title' => trans('messages.admin.api.api_info_cat')])
                </div>
                <div class="col-sm-2" style="text-align: center">
                    <label class="control-label">
                        {!! trans_choice('messages.headers.et', 1) !!}
                    </label>
                    @include('v1.parts.tooltip', ['title' => trans('messages.admin.api.api_info_et')])
                </div>
                <div class="col-sm-2" style="text-align: center">
                    <label class="control-label">@lang('messages.admin.api.api_times')</label>
                </div>
                <div class="col-sm-2" style="text-align: center">
                    <label class="control-label">@lang('messages.headers.pdu_detail')</label>
                </div>
                <div class="col-sm-2" style="text-align: center">
                    <label class="control-label">@lang('messages.fields.memprice')</label>
                </div>
                <div class="col-sm-2" style="text-align: center">
                    <label class="control-label">@lang('messages.fields.nonprice')</label>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-2">
                    <input type="checkbox" class="form-control input-sm" v-model="choices"
                           id="{{ trans('messages.fields.category') }}"
                           value="{{ trans('messages.fields.category') }}"
                           name="{{ trans('messages.fields.category') }}">
                </div>
                <div class="col-sm-2">
                    <input type="checkbox" class="form-control input-sm" v-model="choices"
                           id="{{ trans_choice('messages.headers.et', 1) }}"
                           value="{{ trans_choice('messages.headers.et', 1) }}"
                           name="{{ trans_choice('messages.headers.et', 1) }}">
                </div>
                <div class="col-sm-2">
                    <input type="checkbox" class="form-control input-sm" v-model="choices"
                           id="{{ trans('messages.admin.api.api_times') }}"
                           value="{{ trans('messages.admin.api.api_times') }}"
                           name="{{ trans('messages.admin.api.api_times') }}">
                </div>
                <div class="col-sm-2">
                    <input type="checkbox" class="form-control input-sm" v-model="choices"
                           id="{{ trans('messages.headers.pdu_detail') }}"
                           value="{{ trans('messages.headers.pdu_detail') }}"
                           name="{{ trans('messages.headers.pdu_detail') }}">
                </div>
                <div class="col-sm-2">
                    <input type="checkbox" class="form-control input-sm" v-model="choices"
                           id="{{ trans('messages.fields.memprice') }}"
                           value="{{ trans('messages.fields.memprice') }}"
                           name="{{ trans('messages.fields.memprice') }}">
                </div>
                <div class="col-sm-2">
                    <input type="checkbox" class="form-control input-sm" v-model="choices"
                           id="{{ trans('messages.fields.nonprice') }}"
                           value="{{ trans('messages.fields.nonprice') }}"
                           name="{{ trans('messages.fields.nonprice') }}">
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <p>
                @lang('messages.admin.api.api_instr2')
            </p>

            <draggable tag="ul" v-model="choices" @start="drag=true" @end="drag=false" handle=".handle">
                <div class="list-group-item" v-for="choice in choices">
                    @{{ choice }} <i class="handle fas fa-arrows-alt pull-right"></i>
                </div>
            </draggable>

        </div>
        <div class="form-group col-sm-12">
            Show me choices: @{{ choices }}
            <p>
            </p>
        </div>
    </form>
    </template>
</div>

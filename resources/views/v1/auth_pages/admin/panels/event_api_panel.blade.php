@php
    /**
     * Comment:
     * Created: 11/4/2020
     *
     * @var $org_admin_props
     */

use App\Models\AdminProp;

$sep = AdminProp::find(1);
$header = AdminProp::find(2);

$sep = $org_admin_props->where('propID', 1)->first();
$header = $org_admin_props->where('propID', 2)->first();
$btn_txt = $org_admin_props->where('propID', 3)->first();
$btn_color = $org_admin_props->where('propID', 4)->first();
$hdr_color = $org_admin_props->where('propID', 5)->first();
$btn_size = $org_admin_props->where('propID', 6)->first();
$chars = $org_admin_props->where('propID', 8)->first();
$ban_bkgd = $org_admin_props->where('propID', 9)->first();
$ban_btxt = $org_admin_props->where('propID', 10)->first();

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
        <form action="#" @submit.prevent="onSubmit" method="POST">
            {{-- button params and demo --}}
            <div class="col-sm-8">
                <div class="from-group">
                    <div class="col-sm-3">
                        <label class="control-label" for="{{ $ban_bkgd->prop->name }}">
                            @lang($ban_bkgd->prop->displayName)
                        </label>
                    </div>
                    <div class="col-sm-3">
                        <label class="control-label" for="{{ $ban_btxt->prop->name }}">
                            @lang($ban_btxt->prop->displayName)
                        </label>
                    </div>
                    <div class="col-sm-3">
                        <label class="control-label"
                               for="{{ $chars->prop->name }}">@lang($chars->prop->displayName)</label>
                        @include('v1.parts.tooltip', ['title' => trans('messages.admin.api.api_info_char')])
                    </div>
                    <div class="col-sm-3">
                        <label class="control-label" for="{{ $hdr_color->prop->name }}">
                            @lang($hdr_color->prop->displayName)
                        </label>
                    </div>
                    <div class="col-sm-3">
                        @lang('messages.admin.nc')
                        {{--
                        {{ html()->select($ban_bkgd->prop->name, $colors, $ban_bkgd->value)->class("form-control input-sm")->attribute('v-model', 'admin_props[8].value')->attribute('@blur', 'api_update($event.target.name, $event.target.value)') }}
                        --}}
                    </div>
                    <div class="col-sm-3">
                        @lang('messages.admin.nc')
                        {{--
                        {{ html()->select($ban_btxt->prop->name, $colors, $ban_btxt->value)->class("form-control input-sm")->attribute('v-model', 'admin_props[9].value')->attribute('@blur', 'api_update($event.target.name, $event.target.value)') }}
                        --}}
                    </div>
                    <div class="col-sm-3">
                        <input class="form-control input-sm" type="text" size="3" name="{{ $chars->prop->name }}"
                               v-model="chars" value="{{ $chars->value }}"
                               @blur="api_update($event.target.name, $event.target.value)">
                    </div>
                    <div class="col-sm-3">
                        {{ html()->select($hdr_color->prop->name, $colors, $hdr_color->value)->class("form-control input-sm")->attribute('v-model', 'admin_props[4].value')->attribute('@blur', 'api_update($event.target.name, $event.target.value)') }}
                    </div>
                </div>
            </div>
            <div class="col-sm-4 form-group">
                <h2>
                <span v-bind:class="'bg-'+admin_props[8].value + ' text-'+admin_props[9].value">
                        @lang('messages.admin.nc')
                </span>
                </h2>
            </div>
            <div class="col-sm-12">&nbsp;</div>

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
                        <label class="control-label" for="{{ $sep->prop->name }}">@lang($sep->prop->displayName)</label>
                    </div>

                    <div class="col-sm-3">
                        {{ html()->text($btn_txt->prop->name, $btn_txt->value)->class("form-control input-sm")->id($btn_txt->prop->name)->attribute('v-model', 'admin_props[2].value')->attribute('@blur', 'api_update($event.target.name, $event.target.value)') }}
                        {{--
                        '@blur' => 'api_update(admin_props[2].value)'])
                        --}}
                    </div>
                    <div class="col-sm-3">
                        {{ html()->select($btn_color->prop->name, $colors, $btn_color->value)->class("form-control input-sm")->attribute('v-model', 'admin_props[3].value')->attribute('@blur', 'api_update($event.target.name, $event.target.value)') }}
                    </div>
                    <div class="col-sm-3">
                        {{ html()->select($btn_size->prop->name, $sizes, $btn_size->value)->class("form-control input-sm")->attribute('v-model', 'admin_props[5].value')->attribute('@blur', 'api_update($event.target.name, $event.target.value)') }}
                    </div>
                    <div class="col-sm-3">
                        <input class="form-control input-sm" type="text" size="1" name="{{ $sep->prop->name }}"
                               v-model="separator" value="{{ $sep->value }}"
                               @blur="api_update($event.target.name, $event.target.value)">
                    </div>
                </div>
            </div>
            <div class="col-sm-4 form-group">
                <button v-bind:class="'btn btn-block ' + 'btn-' + admin_props[5].value + ' btn-' + admin_props[3].value">
                    @{{ admin_props[2].value }}
                </button>
            </div>
            <div class="col-sm-12">&nbsp;</div>

            {{-- header params and demo --}}
            <div class="col-sm-8">
                <div class="from-group">
                    <div class="col-sm-8">
                        <label class="control-label" for="{{ $header->prop->name }}">
                            @lang($header->prop->displayName)
                        </label>
                    </div>
                    <div class="col-sm-8">
                        <b v-bind:class="'text-'+admin_props[4].value">@{{ admin_props[1].value }}</b>
                    </div>
                </div>
                <div class="col-xs-12">
                    &nbsp;<br />
                    <p>
                        @lang('messages.admin.api.api_instr1')
                    </p>
                </div>
                <div class="form-group">
                    <div v-for="item in checkbox_list" class="col-sm-2" style="text-align: center">
                        <label class="control-label">@{{ item.value }}</label>
                        <a v-if="item.instr" data-toggle="tooltip" :title="item.instr" data-placement="top">
                            <i class="fas fa-info-square purple"></i>
                        </a>
                    </div>
                </div>
                <div class="form-group">
                    <div v-for="item in checkbox_list" class="col-sm-2">
                        <input type="checkbox" class="form-control input-sm" v-model="choices"
                               :id="item.id"
                               :value="item.value"
                               :name="item.id">
                    </div>
                </div>
                <p>&nbsp;</p>
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
                <button class="btn btn-sm btn-primary"
                        data-toggle="modal" data-target="#dynamic_modal" data-target-id="{{ $currentOrg->orgID . "/0/0/0/1" }}">
                    @lang('messages.admin.api.example')
                </button>
                <p>
                </p>
            </div>
        </form>
    </template>
</div>

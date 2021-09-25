@php
/**
 * Comment: VueJS form to build New or Expiring Member Report Link
 * Created: 9/20/2021
 */

@endphp

<div>
    <template>
        <form action="#" @submit.prevent="onSubmit" method="GET">
            <div class="col-xs-1" style="text-align: right; vertical-align: middle;"></div>
            <div class="col-xs-2" style="text-align: right; vertical-align: middle;">
                <h1> {{ trans('messages.headers.profile_vars.labels.label') }} </h1>
                </div>
            <div class="col-xs-2">
                <label for="which" class="control-label">{{ trans('messages.headers.profile_vars.labels.which_label') }}</label>
                <select name="which" v-model="which" class="form-control input-sm">
                    <option value="new">{{ trans('messages.headers.profile_vars.labels.new_mbr') }}</option>
                    <option value="exp">{{ trans('messages.headers.profile_vars.labels.exp_mbr') }}</option>
                </select>
            </div>
            <div class="col-xs-2">
                <label for="days" class="control-label">{{ trans('messages.headers.profile_vars.labels.days_label') }}</label>
                <input list="day_choices" type="text" v-model="days" class="form-control input-sm"
                       onfocus="this.value='';"
                       aria-label="Text input with dropdown options">
                <datalist id="day_choices">
                    <option value="90"/>
                    <option value="60"/>
                    <option value="30"/>
                </datalist>
            </div>
            <div class="col-xs-2">
                <label for="page" class="control-label">{{ trans('messages.headers.profile_vars.labels.page_label') }}</label>
                <select name="which" v-model="page" class="form-control input-sm">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="75">75</option>
                    <option value="100">100</option>
                </select>
            </div>
            <div class="col-xs-2">
                <a v-bind:href="root + which + '/' + days + '/' + page" class="btn btn-lg btn-primary"><b>@lang('messages.headers.profile_vars.labels.go')</b></a>
            </div>

        </form>
    </template>
</div>
<p> &nbsp; </p>

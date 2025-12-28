@php
/**
 * Comment: Buttons displayed in Member list pages
 * Created: 9/6/2021
 *
 * @var $p Person::personID
 * @var $c count
 *
 */

    $profileURL = env('APP_URL') . "/profile/$p";
    $mergeURL = env('APP_URL') . "/merge/p/$p";
@endphp
<nobr>
<a target='_new' href='{{ $profileURL }}' type='button' data-toggle='tooltip' data-placement='top'
title='{{ trans('messages.tooltips.vep') }}' class='btn btn-xs btn-primary'><i class='far fa-fw fa-edit'></i></a>

@if($c > 0)
    <a data-toggle='modal' class='btn btn-xs btn-success' data-target='#dynamic_modal' data-target-id='{{ $p }}'>
        <i class='far fa-fw fa-book' data-toggle='tooltip' data-placement='top'
         title='{{ trans('messages.tooltips.va') }}'></i></a>
@endif

<a href='{{ $mergeURL }}' data-toggle='tooltip' data-placement='top'
 title='{{ trans('messages.tooltips.mr') }}' class='btn btn-xs btn-warning'>
<i class='far fa-fw fa-code-branch'></i></a>

{{ html()->form('POST', url('/become'))->attribute('target', '_blank')->open() }}
{{ html()->hidden('new_id', $p) }}
<button class="btn btn-xs btn-danger" title="{{ trans('messages.nav.ms_become') }}" data-toggle="tooltip"
onclick="return confirm('{{ trans('messages.tooltips.sure_become') }}');">
<i class='fas fa-fw fa-user'></i></button>
{{ html()->form()->close() }}
</nobr>

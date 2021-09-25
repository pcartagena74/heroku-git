@php
    /**
     * Comment: Page to display new or expiring members
     * Created: 9/19/2021
     *
     * @var $topBits
     * @var $members
     * @var $title
     * @var $orgID
     * @var $which
     * @var $days
     */

    if(!isset($topBits)){
        $topBits = '';
    }

$display = $title;
    if(count($members)>0){
        $btn = '<a class="btn btn-md btn-primary pull-right" href="' .
                env('APP_URL') . "/excel/mbrrpt/$orgID/$which/$days" . '"><b>' .
                trans('messages.buttons.down_PDU_list') . "</a>";
    } else {
        $btn = '';
    }
    $display = "<h1>". $display . $btn . "</h1>";


@endphp
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('header')
    <style>
        table {
            border: 1px solid #ccc;
            border-collapse: collapse;
            margin: 0;
            padding: 0;
            width: 100%;
            table-layout: fixed;
        }

        table caption {
            font-size: 1.5em;
            margin: .5em 0 .75em;
        }

        table tr {
        {{--
        background-color: #f8f8f8;
        --}}
            border: 1px solid #ddd;
            padding: .35em;
        }

        table th,
        table td {
            padding: .625em;
            text-align: left;
        }

        {{--
        table th {
            font-size: .85em;
            letter-spacing: .1em;
            text-transform: uppercase;
        }
        --}}

        @media screen and (max-width: 600px) {
            table {
                border: 0;
            }

            table caption {
                font-size: 1.3em;
            }

            table thead {
                border: none;
                clip: rect(0 0 0 0);
                height: 1px;
                margin: -1px;
                overflow: hidden;
                padding: 0;
                position: absolute;
                width: 1px;
            }

            table tr {
                border-bottom: 3px solid #ddd;
                display: block;
                margin-bottom: .625em;
            }

            table td {
                border-bottom: 1px solid #ddd;
                display: block;
                font-size: .8em;
                text-align: left;
            }

            table td::before {
                /*
                * aria-label has no advantage, it won't be read inside a table
                content: attr(aria-label);
                */
                content: attr(data-label);
                float: left;
        {{--
                font-weight: bold;
                text-transform: uppercase;
        --}}
            }

            table td:last-child {
                border-bottom: 0;
            }
        }
    </style>
@endsection

@section('content')

    <div id="el">
        @include('v1.parts.vuejs.new_or_exp_form')
    </div>


    @include('v1.parts.start_content', ['header' => $display, 'subheader' => '', 'w1' => '12',
                                        'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    <div class="container">

        @if(count($members)>0)
            <div style="overflow-x:auto;">
            <table style="border-spacing: 1em; width: 100%"
                   class="table jambo_table table-striped table-responsive">
                <thead>
                <tr>
                    <th scope="col">@lang('messages.fields.name')</th>
                    <th scope="col">@lang('messages.fields.email')</th>
                    <th scope="col">@lang('messages.headers.profile_vars.orgstat1')</th>
                    <th scope="col">
                        @lang('messages.headers.profile_vars.regs')
                        @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.regs'), 'c' => 'text-warning'])
                    </th>
                    <th scope="col">
                        @lang('messages.headers.profile_vars.regs_now')
                        @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.regs_now'), 'c' => 'text-warning'])
                    </th>
                    @if($which == 'new')
                        <th scope="col">@lang('messages.headers.profile_vars.reldate1')</th>
                        <th scope="col">@lang('messages.headers.profile_vars.reldate2')</th>
                    @else
                        <th scope="col">@lang('messages.headers.profile_vars.orgstat4_short')</th>
                        <th scope="col">@lang('messages.headers.profile_vars.reldate2')</th>
                        <th scope="col">@lang('messages.headers.profile_vars.reldate4')</th>
                    @endif
                </tr>
                </thead>
                <tbody>
                @foreach ($members as $member)
                    <tr>
                        <td scope="row" data-label="{{ trans('messages.fields.name') }}">
                        {{ $member->firstName }} {{ $member->lastName }}
                        </td>
                        <td data-label="{{ trans('messages.fields.email') }}">
                        {{ $member->login }}
                        </td>
                        <td data-label="{{ trans('messages.headers.profile_vars.orgstat1') }}">
                        {{ $member->orgperson->OrgStat1 }}
                        </td>
                        <td data-label="{{ trans('messages.headers.profile_vars.regs') }}">
                            {{ $member->registrations_count }}
                        </td>
                        <td data-label="{{ trans('messages.headers.profile_vars.regs_now') }}">
                            {{ $member->regs_this_year }}
                        </td>
                        @if($which == 'new')
                        <td data-label="{{ trans('messages.headers.profile_vars.reldate1') }}">
                        {{ \Carbon\Carbon::parse($member->orgperson->RelDate1)->format('F j, Y') }}
                        </td>
                        <td data-label="{{ trans('messages.headers.profile_vars.reldate3') }}">
                        {{ \Carbon\Carbon::parse($member->orgperson->RelDate3)->format('F j, Y') }}
                        </td>
                        @else
                        <td data-label="{{ trans('messages.headers.profile_vars.orgstat4_short') }}">
                            {{ $member->orgperson->OrgStat4 }}
                        </td>
                        <td data-label="{{ trans('messages.headers.profile_vars.reldate2') }}">
                        {{ \Carbon\Carbon::parse($member->orgperson->RelDate2)->format('F j, Y') }}
                        </td>
                        <td data-label="{{ trans('messages.headers.profile_vars.reldate4') }}">
                        {{ \Carbon\Carbon::parse($member->orgperson->RelDate4)->format('F j, Y') }}
                        </td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
            </div>
        @else
            @lang('messages.datatable.table-empty')
        @endif
    </div>

    {{ $members->links() }}
    @include('v1.parts.end_content')
@endsection

@section('scripts')
    @include('v1.parts.menu-fix', array('path' => url('membership')))
    <script>
        new Vue({
            el: '#el',
            data: {
                which: '{{ $which ?? 'new' }}',
                days: '{{ $days ?? 90 }}',
                page: '{{ $page ?? 25 }}',
                root: '{{ env('APP_URL') }}/membership/'
            },
            methods: {
                onSubmit() {
                }
            },
        });
    </script>
@endsection

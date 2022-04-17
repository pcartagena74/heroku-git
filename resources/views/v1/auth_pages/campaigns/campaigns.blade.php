@php
/**
 * Comment: Page to list campaigns
 * Created: 9/10/2017
 */

use \Illuminate\Support\Facades\DB;

$topBits = '';  // remove this if this was set in the controller
//$topBits = array([3, 'label', 123, 'ctxt', 'rtxt', 0]);
@endphp
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')
<div class="col-md-12 col-sm-12 ">
    <div class="x_panel">
        <div class="x_title">
            <h2>
                @lang('messages.headers.campaign_heading')
                <small>
                    <a class="btn btn-primary btn-sm" href="{{url('campaign/create')}}">
                        @lang('messages.buttons.create_campaign')
                    </a>
                </small>
            </h2>
            <ul class="nav navbar-right panel_toolbox">
                <li>
                    <a class="collapse-link">
                        <i class="fa fa-chevron-up">
                        </i>
                    </a>
                </li>
            </ul>
            <div class="clearfix">
            </div>
        </div>
        <div class="x_content">
            <div id="search-button-group">
                <label>
                    {{ trans('messages.fields.filter_campaign') }}
                </label>
                <button class="btn btn-primary" onclick="changeSearch('draft',this)" type="button">
                    {{ trans('messages.fields.filter_camp_draft') }}
                </button>
                <button class="btn btn-primary" onclick="changeSearch('sent',this)" type="button">
                    {{ trans('messages.fields.filter_camp_sent') }}
                </button>
                <button class="btn btn-primary" onclick="changeSearch('archive',this)" type="button">
                    {{ trans('messages.fields.filter_camp_archive') }}
                </button>
                <a href="javascript:void" id="removeFilter" onclick="removeFilter()" style="display: none">
                    Remove Filter
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-striped jambo_table bulk_action" id="campaign_dt">
                    <thead>
                        <tr class="headings">
                            <th class="column-title">
                                @lang('messages.headers.camp_thumb')
                            </th>
                            <th class="column-title">
                                @lang('messages.headers.camp_title')
                            </th>
                            <th class="column-title">
                                @lang('messages.headers.camp_status')
                            </th>
                            <th class="column-title">
                                @lang('messages.headers.camp_email_sent')
                            </th>
                            <th class="column-title">
                                @lang('messages.headers.camp_email_open')
                            </th>
                            <th class="column-title">
                                @lang('messages.headers.camp_email_click')
                            </th>
                            <th class="column-title no-link last">
                                <span class="nobr">
                                    @lang('messages.headers.camp_list_action')
                                </span>
                            </th>
                        </tr>
                    </thead>
                </table>
                <table class="table table-striped jambo_table bulk_action hidden" id="">
                    <thead>
                        <tr class="headings">
                            <th class="column-title">
                                @lang('messages.headers.camp_thumb')
                            </th>
                            <th class="column-title">
                                @lang('messages.headers.camp_title')
                            </th>
                            <th class="column-title">
                                @lang('messages.headers.camp_status')
                            </th>
                            <th class="column-title">
                                @lang('messages.headers.camp_email_sent')
                            </th>
                            <th class="column-title">
                                @lang('messages.headers.camp_email_open')
                            </th>
                            <th class="column-title">
                                @lang('messages.headers.camp_email_click')
                            </th>
                            <th class="column-title no-link last">
                                <span class="nobr">
                                    @lang('messages.headers.camp_list_action')
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($campaigns as $c)
                        @php
                            if($c->sendDate === null) {
                                $date = $c->createDate->format(trans('messages.app_params.datetime_format'));
                                $status = trans('messages.fields.camp_status_draft',['date'=>$date]);
                            } else {
                                $date = $c->sendDate->format(trans('messages.app_params.datetime_format'));
                                $status = trans('messages.fields.camp_status_sent',['date'=>$date]);
                            }
                            $opens = DB::table('sent_emails')->where('campaignID', $c->campaignID)->sum('opens');
                            $clicks = DB::table('sent_emails')->where('campaignID', $c->campaignID)->sum('clicks');
                            $open = 0;
                            $click = 0;
                            $emails_count = 0;
                            if($c->sendDate){
                                $width_per_open = 0;
                                $width_per_click = 0;
                                if(!empty($c->mailgun->total_sent)){
                                    $emails_count = $c->mailgun->total_sent;
                                }
                                if(!empty($c->mailgun->open)){
                                    $open = $c->mailgun->open;
                                    if($emails_count > 0){
                                        $width_per_open = ($open / $emails_count) * 100;
                                    }
                                }
                                if(!empty($c->mailgun->click)){
                                    $click = $c->mailgun->click;
                                    if($emails_count > 0){
                                        $width_per_click = ($click / $emails_count) * 100;
                                    }
                                }

                            }
                        @endphp
                        <tr class="even pointer campaign-panel-{{$c->campaignID}}">
                            <td class=" ">
                                <a href="{!! url('campaign',[$c->campaignID,'edit']) !!}">
                                    <img class="img-thumbnail" height="70px" src="{{getEmailTemplateThumbnailURL($c)}}" width="70px" />
                                </a>
                            </td>
                            <td>
                                {!! $c->title !!}
                            </td>
                            <td class=" ">
                                {{$status}}
                            </td>
                            <td class=" ">
                                {{$emails_count}}
                            </td>
                            <td class=" ">
                                {{$opens}}
                            </td>
                            <td class=" ">
                                {{$clicks}}
                            </td>
                            <td class="last">
                                @if($c->sendDate === null)
                                <a class="btn btn-primary btn-sm" href="{!! url('campaign',[$c->campaignID,'edit']) !!}" title="@lang('messages.buttons.common_edit')">
                                    <i aria-hidden="true" class="fa fa-edit">
                                    </i>
                                </a>
                                @else
                                <a class="btn btn-primary btn-sm" href="{!! url('campaign',[$c->campaignID,'edit']) !!}" title="@lang('messages.buttons.common_view')">
                                    <i aria-hidden="true" class="fa fa-eye">
                                    </i>
                                </a>
                                @endif
                                <a class="btn btn-success btn-sm" href="{!! url('campaign',[$c->campaignID,'copy']) !!}" title="@lang('messages.buttons.common_copy')">
                                    <i class="fa fa-copy">
                                    </i>
                                </a>
                                <a class="btn btn-danger btn-sm" href="javascript:void(0)" onclick="deleteCampaign('{{$c->title}}','{{$c->campaignID}}')" title="@lang('messages.buttons.common_delete')">
                                    <i class="fa fa-close">
                                    </i>
                                </a>
                                @if($c->sendDate !== null)
                                <a class="btn btn-warning btn-sm" href="javascript:void(0)" onclick="archiveCampaign('{{$c->title}}','{{$c->campaignID}}')" title="@lang('messages.buttons.archive')">
                                    <i aria-hidden="true" class="fa fa-archive">
                                    </i>
                                </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="footer">
                    </tfoot>
                </table>
                {!! $campaigns->render() !!}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@include('v1.parts.footer-datatable')
<script>
    $('.collapsed').css('height', 'auto');
        $('.collapsed').find('.x_content').css('display', 'none');
    var delete_campaign_id = '';
    function deleteCampaign(title,id){
        delete_campaign_id = id;
        $('#popup-confirm-campaign-delete').find('.modal-body').html('{{trans('messages.campaign_delete_popup.body')}} '+ title + ' ?')
        $('#popup-confirm-campaign-delete').find('.modal-footer .btn-warning').text('{{trans('messages.campaign_delete_popup.btn_ok')}}')
        $('#popup-confirm-campaign-delete').modal('show');
    }

    function setExitPopButtonValue(btn_press){
        switch(btn_press) {
          case 'yes':
            $('#popup-confirm-campaign-delete').modal('hide');
            $.ajax({
                url: '{{ url('deleteCampaign') }}',
                type: 'POST',
                dataType: 'json',
                data: {
                    campaign:delete_campaign_id
                },
                success: function(data) {
                    if(data.success == false){
                        $('#popup-campaign-delete-success').find('.modal-title').html("{{ trans('messages.campaign_delete_popup_success.title_error') }}");
                        $('#popup-campaign-delete-success').find('.modal-body').html(data.message)
                        $('#popup-campaign-delete-success').modal('show');
                    } else {
                        $('.campaign-panel-'+delete_campaign_id).hide();
                        $('#popup-campaign-delete-success').find('.modal-title').html("{{ trans('messages.campaign_delete_popup_success.title') }}");
                        $('#popup-campaign-delete-success').find('.modal-body').html(data.message)
                        $('#popup-campaign-delete-success').modal('show');
                        table.draw();
                    }
                    delete_campaign_id = '';
                },
                error: function(error) {
                    console.log(error)
                }
            });
            break;
            case 'archive':
            $('#popup-confirm-campaign-delete').modal('hide');
                $.ajax({
                    url: '{{ url('archiveCampaign') }}',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        campaign:archive_campaign_id
                    },
                    success: function(data) {
                        if(data.success == false){
                            $('#popup-campaign-delete-success').find('.modal-title').html("{{ trans('messages.campaign_delete_popup_success.title_error') }}");
                            $('#popup-campaign-delete-success').find('.modal-body').html(data.message)
                            $('#popup-campaign-delete-success').modal('show');
                        } else {
                            $('.campaign-panel-'+archive_campaign_id).hide();
                            $('#popup-campaign-delete-success').find('.modal-title').html("{{ trans('messages.campaign_delete_popup_success.title') }}");
                            $('#popup-campaign-delete-success').find('.modal-body').html(data.message)
                            $('#popup-campaign-delete-success').modal('show');
                            table.draw();
                        }
                        archive_campaign_id = '';
                    },
                    error: function(error) {
                        console.log(error)
                    }
                });
            break;
        }
    }

    var archive_campaign_id = '';
    function archiveCampaign(title,id){
        archive_campaign_id = id;
        $('#popup-confirm-campaign-delete').find('.modal-body').html('{{trans('messages.campaign_archive_popup.body')}} '+ title + ' ?')
        $('#popup-confirm-campaign-delete').find('.modal-footer .btn-warning').text('{{trans('messages.campaign_archive_popup.btn_ok')}}');
        $('#popup-confirm-campaign-delete').find('.modal-footer .btn-warning').attr("onclick","setExitPopButtonValue('archive')");
        $('#popup-confirm-campaign-delete').modal('show');
    }
    var table;
    $(document).ready( function () {
        table = $('#campaign_dt').DataTable({
           processing: true,
           serverSide: true,
           responsive: true,
           ajax: { 
                url: "{{ url('list_campaign') }}",
                data: {
                    'filter':$('#filter_status').val()
                }
            },
           ordering:false,
           language: {
                decimal:        "{{ trans('messages.datatable.table-decimal') }}",
                emptyTable:     "{{ trans('messages.datatable.table-empty') }}",
                info:           "{{ trans('messages.datatable.table-info') }}",
                infoEmpty:      "{{ trans('messages.datatable.table-info-empty') }}",
                infoFiltered:   "{{ trans('messages.datatable.table-info-filtered') }}",
                infoPostFix:    "{{ trans('messages.datatable.table-info-postfix') }}",
                thousands:      "{{ trans('messages.datatable.table-thousands') }}",
                lengthMenu:     "{{ trans('messages.datatable.table-length-menu') }}",
                loadingRecords: "{{ trans('messages.datatable.table-loading-results') }}",
                processing:     "{{ trans('messages.datatable.table-processing') }}",
                search:         "{{ trans('messages.datatable.table-search') }}",
                zeroRecords:    "{{ trans('messages.datatable.table-zero-records') }}",
                paginate: {
                    first:      "{{ trans('messages.datatable.table-paginate-first') }}",
                    last:       "{{ trans('messages.datatable.table-paginate-last') }}",
                    next:       "{{ trans('messages.datatable.table-paginate-next') }}",
                    previous:   "{{ trans('messages.datatable.table-paginate-prev') }}"
                },
                aria: {
                    sortAscending:  "{{ trans('messages.datatable.table-aria-sort-asc') }}",
                    sortDescending: "{{ trans('messages.datatable.table-aria-sort-desc') }}"
                },
            },
           columns: [
                    { data: 'thumb', name: '@lang('messages.headers.camp_thumb')' ,searchable: false },
                    { data: 'title', name: '@lang('messages.headers.camp_title')' },
                    { data: 'status', name: '@lang('messages.headers.camp_status')' },
                    { data: 'total_sent', name: '@lang('messages.headers.camp_email_sent')',searchable: false  },
                    { data: 'open', name: '@lang('messages.headers.camp_email_open')' ,searchable: false },
                    { data: 'click', name: '@lang('messages.headers.camp_email_click')',searchable: false },
                    { data: 'action', name: '@lang('messages.headers.camp_list_action')',searchable: false  },
                 ],
        });

     });
    function removeFilter(){
        table.columns(2).search('').draw(true);
        $('#search-button-group .btn').removeClass('active');
        $('#removeFilter').hide();
    }
    function changeSearch(search_by,ths){
        $('#removeFilter').show();
        $('#search-button-group .btn').removeClass('active');
        $(ths).addClass('active');
        table.columns(2).search(search_by).draw(true);
        $('#removeFilter').show();
    }
</script>
@endsection

@section('modals')
@include('v1.modals.context_sensitive_issue')
<div aria-hidden="true" aria-labelledby="popup-confirm-campaign-delete" class="modal fade" id="popup-confirm-campaign-delete" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" class="close" data-dismiss="modal" type="button">
                    <span aria-hidden="true">
                        ×
                    </span>
                </button>
                <h4 class="modal-title">
                    {{ trans('messages.campaign_delete_popup.title') }}
                </h4>
            </div>
            <div class="modal-body">
                <p>
                    {{ trans('messages.campaign_delete_popup.body') }}
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-warning" onclick="setExitPopButtonValue('yes')" type="button">
                    {{ trans('messages.campaign_delete_popup.btn_ok') }}
                </button>
                <button class="btn btn-success" data-dismiss="modal" type="button">
                    {{ trans('messages.campaign_delete_popup.btn_cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>
<div aria-hidden="true" aria-labelledby="popup-campaign-delete-success" class="modal fade" id="popup-campaign-delete-success" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" class="close" data-dismiss="modal" type="button">
                    <span aria-hidden="true">
                        ×
                    </span>
                </button>
                <h4 class="modal-title">
                    {{ trans('messages.campaign_delete_popup_success.title') }}
                </h4>
            </div>
            <div class="modal-body">
                <p>
                    {{ trans('messages.campaign_delete_popup_success.body') }}
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-warning" data-dismiss="modal" type="button">
                    {{ trans('messages.campaign_delete_popup_success.btn_ok') }}
                </button>
            </div>
        </div>
    </div>
</div>
{{--
<div aria-hidden="true" aria-labelledby="campaign_label" class="modal fade" id="campaign_modal" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-label="Close" class="close" data-dismiss="modal" style="float:right;" type="button">
                    <span aria-hidden="true">
                        ×
                    </span>
                </button>
                <h4 class="modal-title" id="campaign_label">
                    Campaign Editor
                </h4>
            </div>
            <div class="modal-body">
                {!! Form::open(array('url' =>env('APP_URL')."/campaign", 'method' => 'get')) !!}
                <div class="col-md-3 col-sm-3 col-xs-12">
                    <button class="btn btn-sm btn-warning" id="add_row" type="button">
                        Add Another
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-dismiss="modal" type="button">
                    Close
                </button>
                <button class="btn btn-sm btn-success" id="addr_submit" type="submit">
                    Save Address
                </button>
            </div>
        </div>
    </div>
</div>
--}}
@endsection

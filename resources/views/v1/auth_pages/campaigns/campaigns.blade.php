<?php
/**
 * Comment: Page to list campaigns
 * Created: 9/10/2017
 */

use \Illuminate\Support\Facades\DB;

$topBits = '';  // remove this if this was set in the controller
//$topBits = array([3, 'label', 123, 'ctxt', 'rtxt', 0]);
?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    @include('v1.parts.start_content', ['header' => 'Campaigns', 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

    <a href="{{ env('APP_URL') }}/campaign/create" class="btn btn-primary btn-lg" style="float:right;">
        Create Campaign
    </a>

    @foreach($campaigns as $c)
<?php
        if($c->sendDate === null) {
            $status = "DRAFT";
        } else {
            $status = "SENT";
        }
        $opens = DB::table('sent_emails')->where('campaignID', $c->campaignID)->sum('opens');
        $clicks = DB::table('sent_emails')->where('campaignID', $c->campaignID)->sum('clicks');
?>
        @if($c == $campaigns->first())
            <div class="col-md-10 col-xs-10">
                <div class="x_panel">
                    <div class="x_title">
                        <h2><a href="{!! env('APP_URL') !!}/campaign/{!! $c->campaignID !!}/edit">{!! $c->title !!}</a>
                            &nbsp;
                            <small>
                                {!! $status !!}
                                @if($c->sendDate)
                                    on {!! $c->sendDate->format('M j, Y') !!}
                                @else
                                    created on {!! $c->createDate->format('n/j/Y') !!}
                                @endif
                            </small>
                        </h2>
                        <ul class="nav navbar-right panel_toolbox">
                            <li><a class="collapse-link"><i class="fa fa-chevron-down"></i></a></li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                   aria-expanded="false"><i class="fa fa-wrench"></i></a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="#">Copy</a></li>
                                    @if($c->sendDate)
                                        <li><a href="#">Delete</a></li>
                                    @else
                                        <li><a href="#">Edit</a></li>
                                    @endif
                                </ul>
                            </li>
                            <li><a class="close-link"><i class="fa fa-close"></i></a></li>
                        </ul>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        @if($c->sendDate)
                            <div class="row tile_count">
                                <div style="text-align: center;" class="col-sm-2 tile_stats_count">
                                    <div class="count green tiles-stats">
                                        {{ $opens }}
                                    </div>
                                    <small>OPENS</small>
                                </div>
                                <div class="col-sm-3">
                                    <br/>
                                    <small>OPEN RATE</small>
                                    <div class="progress progress_sm">
                                        <div class="progress-bar bg-green" role="progressbar"
                                             aria-valuemin="0" aria-valuemax="100"
                                             aria-valuenow="{{ $c->emails_count }}">
                                        </div>
                                    </div>
                                    {{--
                                    <div class="progress progress_sm">
                                        <div class="progress-bar bg-blue" role="progressbar"
                                             aria-valuemin="0" aria-valuemax="100"
                                             aria-valuenow="{{ $c->emails_count }}">
                                        </div>
                                    </div>
                                    <small>AVERAGE OPENS PER EMAIL</small>
                                    --}}
                                </div>

                                <div style="text-align: center;" class="col-sm-2 tile_stats_count">
                                    <div class="count green tiles-stats">
                                        {!! $clicks !!}
                                    </div>
                                    <small>CLICKS</small>
                                </div>

                                <div class="col-sm-3">
                                    <br/>
                                    <small>CLICK RATE</small>
                                    <div class="progress progress_sm">
                                        <div class="progress-bar bg-green" role="progressbar"
                                             aria-valuemin="0" aria-valuemax="100"
                                             aria-valuenow="{{ $c->emails_count }}">
                                        </div>
                                    </div>
                                    {{--
                                    <div class="progress progress_sm">
                                        <div class="progress-bar bg-blue" role="progressbar"
                                             aria-valuemin="0" aria-valuemax="100"
                                             aria-valuenow="{{ $c->emails_count }}">
                                        </div>
                                    </div>
                                    <small>AVERAGE CLICKS PER URL</small>
                                    --}}
                                </div>

                                <div style="text-align: center;" class="col-sm-2 tile_stats_count">
                                    <div class="count green tiles-stats">
                                        {!! $c->emails_count !!}
                                    </div>
                                    <small>EMAILS SENT</small>
                                </div>
                            </div>
                        @endif
                    </div> <!-- x_content -->
                </div>    <!-- x_panel -->
            </div>
        @else
            <div class="col-md-10 col-xs-10">
                <div class="x_panel collapsed">
                    <div class="x_title">
                        <h2>{!! $c->title !!} &nbsp;
                            <small>
                                {!! $status !!}
                                @if($c->sendDate)
                                    on {!! $c->sendDate->format('M j, Y') !!}
                                @else
                                    created on {!! $c->createDate->format('n/j/Y') !!}
                                @endif
                            </small>
                        </h2>
                        <ul class="nav navbar-right panel_toolbox">
                            <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                   aria-expanded="false"><i class="fa fa-wrench"></i></a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="#">Copy</a></li>
                                    @if($c->sendDate)
                                        <li><a href="#">Delete</a></li>
                                    @else
                                        <li><a href="#">Edit</a></li>
                                    @endif
                                </ul>
                            </li>
                            <li><a class="close-link"><i class="fa fa-close"></i></a></li>
                        </ul>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        @if($c->sendDate)
                            <div class="row tile_count">
                                <div style="text-align: center;" class="col-sm-2 tile_stats_count">
                                    <div class="count green tiles-stats">
                                        {{ $opens }}
                                    </div>
                                    <small>OPENS</small>
                                </div>
                                <div class="col-sm-3">
                                    <small>OPEN RATE</small>
                                    <div class="progress progress_sm">
                                        <div class="progress-bar bg-green" role="progressbar"
                                             aria-valuemin="0" aria-valuemax="100"
                                             aria-valuenow="{{ $c->emails_count }}">
                                        </div>
                                    </div>
                                    <div class="progress progress_sm">
                                        <div class="progress-bar bg-blue" role="progressbar"
                                             aria-valuemin="0" aria-valuemax="100"
                                             aria-valuenow="{{ $c->emails_count }}">
                                        </div>
                                    </div>
                                    <small>AVERAGE USER</small>
                                </div>

                                <div style="text-align: center;" class="col-sm-2 tile_stats_count">
                                    <div class="count green tiles-stats">
                                        {!! $clicks !!}
                                    </div>
                                    <small>CLICKS</small>
                                </div>

                                <div class="col-sm-3">
                                    <small>CLICK RATE</small>
                                    <div class="progress progress_sm">
                                        <div class="progress-bar bg-green" role="progressbar"
                                             aria-valuemin="0" aria-valuemax="100"
                                             aria-valuenow="{{ $c->emails_count }}">
                                        </div>
                                    </div>
                                    <div class="progress progress_sm">
                                        <div class="progress-bar bg-blue" role="progressbar"
                                             aria-valuemin="0" aria-valuemax="100"
                                             aria-valuenow="{{ $c->emails_count }}">
                                        </div>
                                    </div>
                                    <small>AVERAGE USER</small>
                                </div>

                                <div style="text-align: center;" class="col-sm-2 tile_stats_count">
                                    <div class="count green tiles-stats">
                                        {!! $c->emails_count !!}
                                    </div>
                                    <small>EMAILS SENT</small>
                                </div>
                            </div>
                        @endif
                    </div> <!-- x_content -->
                </div>    <!-- x_panel -->
            </div>
        @endif

    @endforeach

    @include('v1.parts.end_content')

@endsection

@section('scripts')
    <script>
        $('.collapsed').css('height', 'auto');
        $('.collapsed').find('.x_content').css('display', 'none');
    </script>
@endsection

@section('modals')
    {{--
            <div class="modal fade" id="campaign_modal" tabindex="-1" role="dialog" aria-labelledby="campaign_label"
                 aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button style="float:right;" type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title" id="campaign_label">Campaign Editor</h4>
                        </div>
                        <div class="modal-body">
                            {!! Form::open(array('url' =>env('APP_URL')."/campaign", 'method' => 'get')) !!}

                                <div class="col-md-3 col-sm-3 col-xs-12">
                                    <button type="button" id="add_row" class="btn btn-sm btn-warning">Add Another</button>
                                </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                            <button type="submit" id="addr_submit" class="btn btn-sm btn-success">Save Address</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
    --}}
@endsection

<?php
/**
 * Comment: This template is used to show a member profile --> either self or, when authorized, others
 * Created: 2/9/2017
 */

use Illuminate\Support\Facades\DB;

$currentPerson = App\Person::find(auth()->user()->id);
$string = '';
$profile_script_url = env('APP_URL') . "/profile/$profile->personID";
$op_script_url = env('APP_URL') . "/op/$profile->personID";
$addrURL = env('APP_URL') . "/address/";
$emailURL = env('APP_URL') . "/email/";
$phoneURL = env('APP_URL') . "/phone/";
$ad_cnt = 0;  $em_cnt = 0; $ph_cnt = 0;

if ($profile->personID == $currentPerson->personID) {
    $display = "My";
} else {
    $display = '<b style="color:red;">' . $profile->firstName . " " . $profile->lastName . "'s</b>";
}

$address_type = DB::select("select addrType as 'text', addrType as 'value' from `address-type`");
$email_type = DB::select("select emailType as 'text', emailType as 'value' from `email-type`");
$country_list = DB::select("select cntryID as 'value', cntryName as 'text' from countries");
$state_list = DB::select("select abbrev as 'text', abbrev as 'value' from state");
$phone_type = DB::select("select phoneType as 'text', phoneType as 'value' from `phone-type`");

$allergens = DB::table('allergens')->select('allergen', 'allergen')->get();
$allergen_array = $allergens->pluck('allergen', 'allergen')->toArray();

$chapters = DB::table('organization')->where('orgID', $profile->defaultOrgID)->select('regionChapters')->first();
$array = explode(',', $chapters->regionChapters);

$i = 0;
foreach ($array as $chap) {
    $i++;
    $chap = trim($chap);
    $affiliation_array[$i] = $chap;
}
?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')
    <div class="col-md-12 col-sm-12 col-xs-12">
        <ul id="myTab" class="nav nav-tabs bar_tabs nav-justified" role="tablist">
            <li class="active"><a href="#tab_content1" id="profile-tab" data-toggle="tab"
                                  aria-expanded="true"><b>Profile Information</b></a></li>
            <li class=""><a href="#tab_content2" id="password-tab" data-toggle="tab"
                            aria-expanded="false"><b>Password Management</b></a></li>
            @if(Entrust::hasRole('Speaker'))
                <li class=""><a href="#tab_content3" id="other-tab" data-toggle="tab"
                                aria-expanded="false"><b>Third Thing</b></a></li>
            @endif
        </ul>

        <div id="tab-content" class="tab-content">
            <div class="tab-pane active" id="tab_content1" aria-labelledby="profile-tab">
                &nbsp;<br/>

                @include('v1.parts.start_content', ['header' => $display . ' Profile Information', 'subheader' => '', 'w1' => '8', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

                <table id="profile_fields" class="table table-striped table-condensed">
                    <thead>
                    <tr>
                        <th style="width: 20%; text-align: left;">Prefix</th>
                        <th style="width: 20%; text-align: left;">First Name</th>
                        <th style="width: 20%; text-align: left;">Middle Name</th>
                        <th style="width: 20%; text-align: left;">Last Name</th>
                        <th style="width: 20%; text-align: left;">Suffix</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td style="text-align: left;"><a href="#" id="prefix"
                                                         data-title="Enter prefix">{{ $profile->prefix }}</a>
                        </td>
                        <td style="text-align: left;">
                            {{-- Check OrgStat1 (PMI ID) to check that PMI provided the first & last name --}}
                            @if($profile->OrgStat1)
                                {!! $profile->firstName or "<i style='color:red;'>Empty</i>" !!}
                                @include('v1.parts.tooltip', ['title' => "You need to contact PMI to change your name."])
                            @else
                                <a href="#" id="firstName" data-title="Enter first name">
                                    {!! $profile->firstName or "<i style='color:red;'>Empty</i>" !!}</a>
                            @endif
                        </td>

                        <td style="text-align: left;">
                            <a href="#" id="midName" data-title="Enter middle name">{{ $profile->midName }}</a>
                        </td>
                        <td style="text-align: left;">
                            @if($profile->OrgStat1)
                                {{ $profile->lastName or "<i style='color:red;'>Empty</i>" }}
                                @include('v1.parts.tooltip', ['title' => "You need to contact PMI to change your name."])
                            @else
                                <a href="#" id="lastName" data-title="Enter last name">
                                    {{ $profile->lastName }}</a>
                            @endif
                        </td>
                        <td style="text-align: left;">
                            <a href="#" id="suffix" data-title="Enter suffix">{{ $profile->suffix }}</a>
                        </td>
                    </tr>
                    <tr>
                        <th style="text-align: left;">Preferred Name</th>
                        <th style="text-align: left;">Industry</th>
                        <th style="text-align: left;">Company</th>
                        <th style="text-align: left;">Title</th>
                        <th style="text-align: left;">
                            Login
                            @include('v1.parts.tooltip', ['title' => "If you want your login to be a new email address, you'll have to first add it by clicking 'Add Email' below."])
                        </th>
                    </tr>
                    <tr>
                        <td style="text-align: left;"><a href="#" id="prefName"
                                                         data-title="Enter preferred name">{{ $profile->prefName }}</a>
                        </td>
                        <td style="text-align: left;"><a href="#" id="indName"
                                                         data-title="Enter industry">{{ $profile->indName }}</a></td>
                        <td style="text-align: left;"><a href="#" id="compName"
                                                         data-title="Enter company name">{{ $profile->compName }}</a>
                        </td>
                        <td style="text-align: left;"><a href="#" id="title"
                                                         data-title="Enter title">{{ $profile->title }}</a></td>
                        <td style="text-align: left;"><a href="#" id="login" data-value="{{ $profile->login }}"></a>
                        </td>
                    </tr>
                    <tr>
                        {{-- Adding new fields to person for profile display will require the update of the show() query --}}

                        <th style="text-align: left;">PM Experience</th>
                        <th style="text-align: left;">Chapter Role</th>
                        <th style="text-align: left;">Chapter Affiliation</th>
                        <th style="text-align: left;">Dietary Restrictions</th>
                        <th style="text-align: left;">Restriction Comments</th>
                    </tr>
                    <tr>
                        <td style="text-align: left;">
                            <a href="#" id="experience"
                               data-title="PM Experience (Years)">{{ $profile->experience }}</a>
                        </td>
                        <td style="text-align: left;">
                            <a href="#" id="chapterRole" data-title="Chapter Role">{{ $profile->chapterRole }}</a>
                        </td>
                        <td style="text-align: left;">
                            <a href="#" id="affiliation"
                               data-title="Chapter Affiliation">{{ $profile->affiliation }}</a>
                        </td>
                        <td style="text-align: left;"><a href="#" id="allergenInfo" ,
                                                         data-title="Dietary Restrictions">{{ $profile->allergenInfo }}</a>
                        </td>
                        <td style="text-align: left;"><a href="#" id="allergenNote" ,
                                                         data-title="Dietary Notes">{{ $profile->allergenNote }}</a>
                        </td>
                    </tr>
                    </tbody>
                </table>
                @include('v1.parts.end_content')


                @if(Entrust::hasRole('Admin'))
                    @include('v1.parts.start_content', ['header' => 'Date Fields', 'subheader' => '<b class="red">(Editable for Admins)</b>', 'w1' => '4', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
                    <table id='date_fields' class='table table-striped table-condensed'>
                        @for($i=1;$i<=10;$i++)
                            @if(isset($profile->{'ODN'.$i}))
                                <tr>
                                    <td style="text-align: left;">{{ $profile->{'ODN'.$i} }}</td>
                                    <td style="text-align: left;">
                                        <a href="#" id="RelDate{{$i}}" data-value="{!! $profile->{'RelDate'.$i} !!}"></a>
                                    </td>
                                </tr>
                            @elseif($i == 1)
                                If this is empty, you may not have a PMI ID on file.
                            @endif
                        @endfor
                    </table>
                    @include('v1.parts.end_content')
                @else
                    @include('v1.parts.start_content', ['header' => 'Date Fields', 'subheader' => '(uneditable)', 'w1' => '4', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
                    <table id='date_fields' class='table table-striped table-condensed'>
                        @for($i=1;$i<=10;$i++)
                            @if(isset($profile->{'ODN'.$i}))
                                <tr>
                                    <td style="text-align: left;">{{ $profile->{'ODN'.$i} }}</td>
                                    <td style="text-align: left;">{!! $profile->{'RelDate'.$i} or "<i style='color:red;'>Empty</i>" !!}</td>
                                </tr>
                            @elseif($i == 1)
                                If this is empty, you may not have a PMI ID on file.
                            @endif
                        @endfor
                    </table>
                    @include('v1.parts.end_content')
                @endif

                @include('v1.parts.start_min_content', ['header' => 'Addresses', 'subheader' => '', 'w1' => '8', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

                @if(count($addresses) == 0)
                    There are no addresses associated with this profile.
                @else
                    <table id="address_fields" class="table table-striped table-condensed">
                        <thead>
                        <tr>
                            <th></th>
                            <th style="width: 10%">Type</th>
                            <th style="width: 20%">Address 1</th>
                            <th style="width: 20%">Address 2</th>
                            <th style="width: 20%">City</th>
                            <th style="width: 10%">State</th>
                            <th style="width: 10%">Zip</th>
                            <th style="width: 10%">Country</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($addresses as $address)
                            <?php $ad_cnt++; ?>
                            <tr>
                                <td>
                                    <form id="ad-{{ $ad_cnt }}" method="post"
                                          action="{{ env('APP_URL') . "/address/" . $address->addrID . "/delete" }}">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="personID" value="{{ $profile->personID }}">
                                        <button class="btn btn-danger btn-xs">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                                <td><a href="#" id="addrTYPE{{ $ad_cnt }}" data-pk="{{ $address->addrID }}"
                                       data-url="{{ $addrURL . $address->addrID }}"
                                       data-title="Enter address type"
                                       data-value="{{ $address->addrTYPE }}">{{ $address->addrTYPE }}</a></td>
                                <td><a href="#" id="addr1{{ $ad_cnt }}" data-pk="{{ $address->addrID }}"
                                       data-url="{{ $addrURL . $address->addrID }}"
                                       data-title="Enter address 1" data-value="{{ $address->addr1 }}"></a></td>
                                <td><a href="#" id="addr2{{ $ad_cnt }}" data-pk="{{ $address->addrID }}"
                                       data-url="{{ $addrURL . $address->addrID }}"
                                       data-title="Enter address 2" data-value="{{ $address->addr2 }}"></a></td>
                                <td><a href="#" id="city{{ $ad_cnt }}" data-pk="{{ $address->addrID }}"
                                       data-title="Enter city"
                                       data-url="{{ $addrURL . $address->addrID }}"
                                       data-value="{{ $address->city }}"></a></td>
                                <td><a href="#" id="state{{ $ad_cnt }}" data-pk="{{ $address->addrID }}"
                                       data-title="Enter state"
                                       data-url="{{ $addrURL . $address->addrID }}"
                                       data-value="{{ $address->state }}"></a></td>
                                <td><a href="#" id="zip{{ $ad_cnt }}" data-pk="{{ $address->addrID }}"
                                       data-title="Enter zip code"
                                       data-url="{{ $addrURL . $address->addrID }}"
                                       data-value="{{ $address->zip }}"></a></td>
                                <td><a href="#" id="cntryID{{ $ad_cnt }}" data-pk="{{ $address->addrID }}"
                                       data-url="{{ $addrURL . $address->addrID }}"
                                       data-title="Enter country" data-value="{{ $address->cntryID }}"></a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
                <div class="col-md-4 col-sm-9 col-xs-12">
                    <button type="button" id="add_address" class="btn btn-sm btn-success"
                            data-toggle="modal" data-target="#address_modal">Add Address
                    </button>
                </div>
                <div class="col-md-4 col-sm-9 col-xs-12" style="text-align: center"></div>
                <div class="col-md-4 col-sm-9 col-xs-12" style="text-align: right"></div>
                @include('v1.parts.end_content')

                @if(Entrust::hasRole('Admin'))
                    @include('v1.parts.start_content', ['header' => 'Custom Fields', 'subheader' => '<b class="red">(Editable for Admins)</b>', 'w1' => '4', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
                    <table id='date_fields' class='table table-striped table-condensed'>
                        @for($i=1;$i<=10;$i++)
                            @if(isset($profile->{'OSN'.$i}))
                                <tr>
                                    <td style="text-align: left;">{{ $profile->{'OSN'.$i} }}</td>
                                    <td style="text-align: left;">
                                        <a href="#" id="OrgStat{{$i}}" data-value="{!! $profile->{'OrgStat'.$i} !!}"></a>
                                    </td>
                                </tr>
                            @elseif($i == 1)
                                If this is empty, you may not have a PMI ID on file.
                            @endif
                        @endfor
                    </table>
                    @include('v1.parts.end_content')
                @else
                    @include('v1.parts.start_content', ['header' => 'Custom Fields', 'subheader' => '(uneditable)', 'w1' => '4', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
                    <table id='date_fields' class='table table-striped table-condensed'>
                        @for($i=1;$i<=10;$i++)
                            @if(isset($profile->{'OSN'.$i}))
                                <tr>
                                    <td style="text-align: left;">{{ $profile->{'OSN'.$i} }}</td>
                                    <td style="text-align: left;">{!! $profile->{'OrgStat'.$i} or "<i style='color:red;'>Empty</i>" !!}</td>
                                </tr>
                            @elseif($i == 1)
                                If this is empty, you may not have a PMI ID on file.
                            @endif
                        @endfor
                    </table>
                    @include('v1.parts.end_content')
                @endif

                @include('v1.parts.start_min_content', ['header' => 'Email Addresses', 'subheader' => '', 'w1' => '8', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
                @if(count($emails) == 0)
                    There are no emails associated with this profile.
                @else
                    These email addresses are those that you may have used to register for an event.  <p>

                    <table id="email_fields" class="table table-striped table-condensed">
                        <tr>
                            <th style="text-align:center;" colspan="2">Type</th>
                            <th style="text-align:left;">Email</th>
                            <th style="text-align:left;">
                                Primary?
                                @include('v1.parts.tooltip', ['title' => "The primary address is the only one we'll use to contact you. It is also the email address selected above."])
                            </th>
                        </tr>
                        @foreach($emails as $email)
                            <?php $em_cnt++; ?>
                            <tr>
                                <td style="text-align: left;">
                                    @if($email->isPrimary)
                                        <button class="btn btn-danger btn-xs" disabled><i class="fa fa-trash"></i>
                                        </button>
                                        @include('v1.parts.tooltip', ['title' => "You cannot delete this address because it is your primary address (and login).  Change the login address above first."])
                                    @else
                                        <form method="post"
                                              action="{{ env('APP_URL') . "/email/" . $email->emailID . "/delete" }}">
                                            {{ csrf_field() }}
                                            <input type="hidden" name="personID" value="{{ $profile->personID }}">
                                            <button class="btn btn-danger btn-xs" data-toggle="confirmation"
                                                    data-btn-ok-label="Continue"
                                                    data-btn-ok-icon="glyphicon glyphicon-share-alt"
                                                    data-btn-cancel-label="Stop!" data-id="em-{{ $em_cnt }}"
                                                    data-btn-cancel-icon="glyphicon glyphicon-ban-circle"
                                                    data-title="Are you sure?" data-content="This cannot be undone.">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                                <td style="text-align: left;"><a href="#" id="emailTYPE{{ $em_cnt }}"
                                                                 data-pk="{{ $email->emailID }}"
                                                                 data-url="{{ $emailURL . $email->emailID }}"
                                                                 data-title="Enter email type"
                                                                 data-value="{{ $email->emailTYPE }}"></a>
                                </td>
                                <td style="text-align: left;"><a href="#" id="emailADDR{{ $em_cnt }}"
                                                                 data-pk="{{ $email->emailID }}"
                                                                 data-url="{{ $emailURL . $email->emailID }}"
                                                                 data-title="Enter address 1">{{ $email->emailADDR }}</a>
                                </td>
                                <td style="text-align: left;">@if($email->isPrimary) Yes
                                    @else
                                        No
                                    @endif

                                </td>
                            </tr>
                        @endforeach
                    </table>
                @endif
                <div class="col-md-4 col-sm-9 col-xs-12">
                    <button type="button" id="add_email" class="btn btn-sm btn-success" data-toggle="modal"
                            data-target="#email_modal">Add Email
                    </button>
                </div>
                <div class="col-md-4 col-sm-9 col-xs-12" style="text-align: center"></div>
                <div class="col-md-4 col-sm-9 col-xs-12" style="text-align: right"></div>
                <p>&nbsp;</p>
                <p>&nbsp;</p>
                @include('v1.parts.end_content')

                @include('v1.parts.start_min_content', ['header' => 'Phone Numbers', 'subheader' => '', 'w1' => '4', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

                @if(count($phones) == 0)
                    There are no phone numbers associated with this profile.
                @else

                    <table id="phone_fields" class="table table-striped table-condensed">
                        <tr>
                            <th style="text-align:center;" colspan="2">Type</th>
                            <th style="text-align:left;">Phone Number</th>
                        </tr>

                        @foreach($phones as $phone)
                            <?php $ph_cnt++; ?>
                            <tr>
                                <td style="text-align: left;">
                                    <form id="ph-{{ $ph_cnt }}" method="post"
                                          action="{{ env('APP_URL') . "/phone/" . $phone->phoneID . "/delete" }}">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="personID" value="{{ $profile->personID }}">
                                        <button class="btn btn-danger btn-xs" data-toggle="confirmation"
                                                data-btn-ok-label="Continue"
                                                data-btn-ok-icon="glyphicon glyphicon-share-alt"
                                                data-btn-cancel-label="Stop!" data-id="ph-{{ $ph_cnt }}"
                                                data-btn-cancel-icon="glyphicon glyphicon-ban-circle"
                                                data-title="Are you sure?" data-content="This cannot be undone.">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                                <td style="text-align: left;"><a href="#" id="phoneType{{ $ph_cnt }}"
                                                                 data-pk="{{ $phone->phoneID }}"
                                                                 data-url="{{ $phoneURL . $phone->phoneID }}"
                                                                 data-value="{{ $phone->phoneType }}"></a></td>
                                <td style="text-align: left;"><a href="#" id="phoneNumber{{ $ph_cnt }}"
                                                                 data-pk="{{ $phone->phoneID }}"
                                                                 data-url="{{ $phoneURL . $phone->phoneID }}"
                                                                 data-value="{{ $phone->phoneNumber }}"></a></td>
                            </tr>
                        @endforeach
                    </table>
                @endif
                <div class="col-md-4 col-sm-9 col-xs-12">
                    <button type="button" id="add_email" class="btn btn-sm btn-success" data-toggle="modal"
                            data-target="#phone_modal">Add Phone Number
                    </button>
                </div>
                <div class="col-md-4 col-sm-9 col-xs-12" style="text-align: right"></div>
                <p>&nbsp;</p>
                @include('v1.parts.end_content')

            </div>
            <div class="tab-pane fade" id="tab_content2" aria-labelledby="password-tab">
                &nbsp;<br/>
                <div class="col-sm-12">


                    @if(Entrust::hasRole('Admin'))
                        @include('v1.parts.start_content', ['header' => 'Changing ' . $display . ' Password',
                            'subheader' => '', 'w1' => '8', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

                        {!! Form::open(array('url' => env('APP_URL')."/force_password", 'method' => 'POST')) !!}
                        <div class="form-group">
                            {!! Form::label('userid', 'User ID', array('class' => 'control-label')) !!}
                            {!! Form::number('userid', $profile->personID, $attributes = array('class' => 'form-control', 'required')) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('newPass', 'New Password', array('class' => 'control-label')) !!}
                            {!! Form::password('password', $attributes = array('class' => 'form-control', 'required')) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('password_confirmation', 'Verify Password', array('class' => 'control-label')) !!}
                            {!! Form::password('password_confirmation', $attributes = array('class' => 'form-control', 'required')) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::submit('Change Password', array('class' => 'btn btn-primary btn-sm')) !!}
                        </div>
                        {!! Form::close() !!}

                        @include('v1.parts.end_content')
                    @else
                        {!! Form::open(array('url' => env('APP_URL')."/password", 'method' => 'POST')) !!}
                        <div class="form-group">
                            {!! Form::label('curPass', 'Current Password', array('class' => 'control-label')) !!}
                            {!! Form::password('curPass', $attributes = array('class' => 'form-control', 'required')) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('newPass', 'New Password', array('class' => 'control-label')) !!}
                            {!! Form::password('password', $attributes = array('class' => 'form-control', 'required')) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('password_confirmation', 'Verify Password', array('class' => 'control-label')) !!}
                            {!! Form::password('password_confirmation', $attributes = array('class' => 'form-control', 'required')) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::submit('Change Password', array('class' => 'btn btn-primary btn-sm')) !!}
                        </div>
                        {{-- current, new, verify --}}
                        {!! Form::close() !!}
                    @endif

                </div>
            </div>
            @if(Entrust::hasRole('Speaker'))
                <div class="tab-pane fade" id="tab_content3" aria-labelledby="other-tab">
                    &nbsp;<br/>
                    <b>Speaker features will appear here in the next update.</b>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $('.collapsed').css('height', 'auto');
        $('.collapsed').find('.x_content').css('display', 'none');
    </script>
    <script>
        //redirection to a specific tab
        $(document).ready(function () {
            $('#myTab a[href="#{{ old('tab') }}"]').tab('show')
        });
    </script>
    @if($profile->personID !== auth()->user()->id)
        @include('v1.parts.menu-fix', array('path' => '/members', 'tag' => '#mem', 'newTxt' => 'Edit Member Profile'))
    @endif
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('[data-toggle="tooltip"]').tooltip({'placement': 'top'});
            $.fn.editable.defaults.mode = 'inline';
            $.fn.editable.defaults.params = function (params) {
                params._token = $("meta[name=token]").attr("content");
                return params;
            };

            $('#experience').editable({
                type: 'select',
                autotext: 'auto',
                pk: '{{ $profile->personID }}',
                url: '{{ $profile_script_url }}',
                <?php
                    if ($profile->experience <> "") {
                        echo("value: '$profile->experience',\n");
                    }
                    ?>
                source: [
                    {value: '1-4', text: '1-4 Years'},
                    {value: '5-9', text: '5-9 Years'},
                    {value: '10-14', text: '10-14 Years'},
                    {value: '15-19', text: '15-19 Years'},
                    {value: '20+', text: '20+ Years'}
                ]
            });

            $('#prefix').editable({
                type: 'select',
                autotext: 'auto',
                pk: '{{ $profile->personID }}',
                url: '{{ $profile_script_url }}',
<?php
                    if ($profile->prefix <> "") {
                        echo("value: '$profile->prefix', \n");
                    }
?>
                source: [
<?php
                    foreach ($prefixes as $row) {
                        $string .= "{ value: '" . $row->prefix . "' , text: '" . $row->prefix . "' },\n";
                    }
?>
                    {!!  rtrim($string, ",") !!}  <?php $string = ''; ?>
                ]
            });
            @if(!isset($profile->OrgStat1))
            $('#firstName').editable({
                type: 'text',
                pk: '{{ $profile->personID }}',
                url: '{{ $profile_script_url }}'
            });
            $('#lastName').editable({
                type: 'text',
                pk: '{{ $profile->personID }}',
                url: '{{ $profile_script_url }}'
            });
            @endif

            $('#midName').editable({
                type: 'text',
                pk: '{{ $profile->personID }}',
                url: '{{ $profile_script_url }}'
            });

            $('#suffix').editable({
                type: 'text',
                pk: '{{ $profile->personID }}',
                url: '{{ $profile_script_url }}'
            });
            $('#prefName').editable({
                type: 'text',
                pk: '{{ $profile->personID }}',
                url: '{{ $profile_script_url }}'
            });

            $('#indName').editable({
                type: 'select',
                autotext: 'auto',
                pk: '{{ $profile->personID }}',
                url: '{{ $profile_script_url }}',
                <?php
                    if ($profile->indName <> "") {
                        echo("value: '$profile->indName', \n");
                    }
                    ?>
                source: [
                    <?php
                    foreach ($industries as $row) {
                        $string .= "{ value: '" . $row->industryName . "' , text: '" . $row->industryName . "' },";
                    }
                    ?>
                    {!!  rtrim($string, ",") !!}  <?php $string = ''; ?>
                ]
            });

            $('#compName').editable({
                type: 'text',
                pk: '{{ $profile->personID }}',
                url: '{{ $profile_script_url }}'
            });
            $('#title').editable({
                type: 'text',
                pk: '{{ $profile->personID }}',
                url: '{{ $profile_script_url }}'
            });
            $('#login').editable({
                type: 'select',
                pk: '{{ $profile->personID }}',
                url: '{{ $profile_script_url }}',
                source: [
                    @foreach($emails as $email)
                    {!! "{ value: '" . $email->emailADDR . "', text: '" . $email->emailADDR . "' }," !!}
                    @endforeach
                ]
            });
            $('#chapterRole').editable({
                type: 'text',
                pk: '{{ $profile->personID }}',
                url: '{{ $profile_script_url }}'
            });

            $('#affiliation').editable({
                type: 'checklist',
                pk: '{{ $profile->personID }}',
                url: '{{ $profile_script_url }}',
                value: '{{ $profile->affiliation }}',
                {{--
                    success: function (response, data) {
                        console.log(response);
                        if(!response){
                            alert('no response');
                        }
                        console.log(data);
                    },
                --}}
                source: [
                    <?php
                    for ($j = 1; $j <= count($affiliation_array); $j++) {
                        $string .= "{ value: '" . $affiliation_array[$j] . "' , text: '" . $affiliation_array[$j] . "' },";
                    }
                    ?>
                    {!!  rtrim($string, ",") !!}  <?php $string = ''; ?>
                ]
            });

            $("#allergenInfo").editable({
                type: 'checklist',
                pk: '{{ $profile->personID }}',
                url: '{{ $profile_script_url }}',
                source: [
                    <?php
                    foreach ($allergen_array as $x) {
                        $string .= "{ value: '" . $x . "' , text: '" . $x . "' },";
                    }
                    ?>
                    {!!  rtrim($string, ",") !!}  <?php $string = ''; ?>
                ]
            });

            $('#allergenNote').editable({
                type: 'text',
                pk: '{{ $profile->personID }}',
                url: '{{ $profile_script_url }}'
            });

            @for($j=1;$j<=$ad_cnt;$j++)
            $('#addrTYPE{{ $j }}').editable({
                type: 'select',
                autotext: 'auto',
                source: [
                    <?php
                    foreach ($addrTypes as $row) {
                        $string .= "{ value: '" . $row->addrType . "' , text: '" . $row->addrType . "' },";
                    }
                    ?>
                    {!!  rtrim($string, ",") !!}  <?php $string = ''; ?>
                ]
            });
            $('#addr1{{ $j }}').editable({type: 'text'});
            $('#addr2{{ $j }}').editable({type: 'text'});
            $('#city{{ $j }}').editable({type: 'text'});
            $('#state{{ $j }}').editable({type: 'text'});
            $('#zip{{ $j }}').editable({type: 'text'});
            $('#cntryID{{ $j }}').editable({
                type: 'select',
                autotext: 'auto',
                source: [
                    <?php
                    foreach ($countries as $row) {
                        $string .= '{ value: "' . $row->cntryID . '" , text: "' . $row->cntryName . '" },';
                    }
                    ?>
                    {!!  rtrim($string, ",") !!}  <?php $string = ''; ?>
                ]
            });
            @endfor

            @for($j=1;$j<=$em_cnt;$j++)
            $('#emailTYPE{{ $j }}').editable({
                type: 'select',
                autotext: 'auto',
                source: [
                    <?php
                    foreach ($emailTypes as $row) {
                        $string .= "{ value: '" . $row->emailType . "' , text: '" . $row->emailType . "' },";
                    }
                    ?>
                    {!!  rtrim($string, ",") !!}  <?php $string = ''; ?>
                ]
            });
            $('#emailADDR{{ $j }}').editable({type: 'text'});
            @endfor

            @for($j=1;$j<=$ph_cnt;$j++)
            $('#phoneType{{ $j }}').editable({
                type: 'select',
                autotext: 'auto',
                source: [
                    <?php
                    foreach ($phoneTypes as $row) {
                        $string .= "{ value: '" . $row->phoneType . "' , text: '" . $row->phoneType . "' },";
                    }
                    ?>
                    {!!  rtrim($string, ",") !!}  <?php $string = ''; ?>
                ]
            });
            $('#phoneNumber{{ $j }}').editable({type: 'text'});
            @endfor
        });
    </script>
    <script>
        $(document).ready(function () {
            var i = 2;
            var x;
            $('#add_row').click(function () {
                if (i <= 5) {
                    $('#delete_row').show();
                    $('#addr_submit').show();
                    x = "addr" + i + "_row";
                    $('#' + x).show();
                    i++;
                }
                if (i >= 3) {
                    $('#addr_submit').text("Save Addresses");
                }
                if (i == 6) {
                    $('#add_row').prop('disabled', true);
                }
            });
            $('#delete_row').click(function () {
                if (i >= 3) {
                    y = i - 1;
                    x = "addr" + y + "_row";
                    $('#' + x).hide();
                    i--;
                    $('#add_row').prop('disabled', false);
                }

                if (i <= 2) {
                    $('#addr_submit').text("Save Address");
                    $('#delete_row').hide();
                }
            });
        });
    </script>
    <script>
        @if(Entrust::hasRole('Admin'))
            @for($i=1;$i<=10;$i++)
                @if(null !== $profile->{'ODN'.$i})
                    $('#RelDate{{ $i }}').editable({
                        type: 'combodate',
                        pk: '{{ $profile->personID }}',
                        url: '{{ $op_script_url }}',
                        template: 'MMM DD YYYY',
                        format: 'YYYY-MM-DD 00:00:00',
                        viewformat: 'MMM DD, YYYY',
                        placement: 'left',
                        //viewformat: 'h:mm A',
                        combodate: {
                            //minYear: '{{ date("Y") }}',
                            maxYear: '{{ date("Y")+3 }}',
                            minuteStep: 15
                        },
                    });
                @endif
            @endfor

            @for($i=1;$i<=10;$i++)
                @if(null !== $profile->{'OSN'.$i})
                    @if($i == 2)
                $('#OrgStat{{ $i }}').editable({
                    type: 'select',
                    source: [
                        {value: 'Individual', text: 'Individual'},
                        {value: 'Retiree', text: 'Retiree'},
                        {value: 'Student', text: 'Student'}
                    ],
                    pk: '{{ $profile->personID }}',
                    url: '{{ $op_script_url }}',
                    placement: 'left',
                });
                    @else
                $('#OrgStat{{ $i }}').editable({
                    type: 'text',
                    pk: '{{ $profile->personID }}',
                    url: '{{ $op_script_url }}',
                    placement: 'left',
                });
                    @endif
                @endif
            @endfor
        @endif

    </script>
    <script>
        $(document).ready(function () {
            var i = 2;
            var x;
            $('#add_erow').click(function () {
                if (i <= 5) {
                    $('#delete_erow').show();
                    $('#email_submit').show();
                    x = "email" + i + "_row";
                    $('#' + x).show();
                    i++;
                }
                if (i >= 3) {
                    $('#email_submit').text("Save Emails");
                }
                if (i == 6) {
                    $('#add_erow').prop('disabled', true);
                }
            });
            $('#delete_erow').click(function () {
                if (i >= 3) {
                    y = i - 1;
                    x = "email" + y + "_row";
                    $('#' + x).hide();
                    i--;
                    $('#add_erow').prop('disabled', false);
                }

                if (i <= 2) {
                    $('#email_submit').text("Save Email");
                    $('#delete_erow').hide();
                }
            });
        });
    </script>
    <script>
        $(document).ready(function () {
            var i = 2;
            var x;
            $('#add_prow').click(function () {
                if (i <= 5) {
                    $('#delete_prow').show();
                    $('#phone_submit').show();
                    x = "phone" + i + "_row";
                    $('#' + x).show();
                    i++;
                }
                if (i >= 3) {
                    $('#phone_submit').text("Save Phone Numbers");
                }
                if (i == 6) {
                    $('#add_prow').prop('disabled', true);
                }
            });
            $('#delete_prow').click(function () {
                if (i >= 3) {
                    y = i - 1;
                    x = "phone" + y + "_row";
                    $('#' + x).hide();
                    i--;
                    $('#add_prow').prop('disabled', false);
                }

                if (i <= 2) {
                    $('#phone_submit').text("Save Phone Number");
                    $('#delete_prow').hide();
                }
            });
        });
    </script>

@endsection

@section('modals')
    <div class="modal fade" id="address_modal" tabindex="-1" role="dialog" aria-labelledby="address_label"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form name="addresses" method="post" action="/addresses/create">
                    <div class="modal-header">
                        <h5 class="modal-title" id="address_label">Add Additional Addresses</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        {{ csrf_field() }}
                        <input type="hidden" name="personID" value="{{ $profile->personID }}">
                        <table id="new_address_fields" class="table table-striped">
                            <thead>
                            <tr>
                                <th style="width: 10%">Type</th>
                                <th style="width: 20%">Address 1 &amp; 2</th>
                                <th style="width: 20%">City</th>
                                <th style="width: 10%">State</th>
                                <th style="width: 10%">Zip</th>
                                <th style="width: 10%">Country</th>
                            </tr>
                            </thead>
                            <tbody>

                            @for($n=1; $n<=5; $n++)

                                <tr id="addr{{ $n }}_row"<?php if ($n > 1) echo(' style="display:none"'); ?>>
                                    <td><select name='addrTYPE-{{ $n }}'>
                                            <option>...</option>
                                            @include('v1.parts.form-option-show', ['array' => $address_type])
                                        </select></td>
                                    <td><input name='addr1-{{ $n }}' type='text' placeholder='Address 1'
                                               class='form-control input-sm'>
                                        <input name='addr2-{{ $n }}' type='text' placeholder='Address 2'
                                               class='form-control input-sm'></td>
                                    <td><input name='city-{{ $n }}' type='text' placeholder='City'
                                               class='form-control input-sm'></td>
                                    <td><select name='state-{{ $n }}'>
                                            <option>...</option>
                                    @include('v1.parts.form-option-show', ['array' => $state_list])
                                    <td><input name='zip-{{ $n }}' type='text' size="5" placeholder='Zip Code'
                                               style="width: 65px" class='form-control input-sm'></td>
                                    <td><select name='cntryID-{{ $n }}' style="width: 50px">
                                            <option>...</option>
                                            @include('v1.parts.form-option-show', ['array' => $country_list])
                                        </select></td>
                                </tr>
                            @endfor
                            </tbody>
                        </table>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <button type="button" id="add_row" class="btn btn-sm btn-warning">Add Another</button>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12" style="text-align: right">
                            <button type="button" style="display: none" id="delete_row" class="btn btn-sm btn-danger">
                                Delete
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                        <button type="submit" id="addr_submit" class="btn btn-sm btn-success">Save Address</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="email_modal" tabindex="-1" role="dialog" aria-labelledby="email_label"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form name="emails" method="post" action="/emails/create">
                    <div class="modal-header">
                        <h5 class="modal-title" id="address_label">Add Additional Email</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        {{ csrf_field() }}
                        <input type="hidden" name="personID" value="{{ $profile->personID }}">
                        <table id="new_email_fields" class="table table-striped">
                            <thead>
                            <tr>
                                <th style="width: 10%">Type</th>
                                <th style="width: 20%">Email Address</th>
                            </tr>
                            </thead>
                            <tbody>

                            @for($n=1; $n<=5; $n++)

                                <tr id="email{{ $n }}_row"<?php if ($n > 1) echo(' style="display:none"'); ?>>
                                    <td><select name='emailTYPE-{{ $n }}'>
                                            <option>...</option>
                                            @include('v1.parts.form-option-show', ['array' => $address_type])
                                        </select></td>
                                    <td><input name='emailADDR-{{ $n }}' type='email' placeholder='Email Address'
                                               class='form-control input-sm'>
                                </tr>

                            @endfor

                            </tbody>
                        </table>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <button type="button" id="add_erow" class="btn btn-sm btn-warning">Add Another</button>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12" style="text-align: right">
                            <button type="button" style="display: none" id="delete_erow" class="btn btn-sm btn-danger">
                                Delete
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                        <button type="submit" id="email_submit" class="btn btn-sm btn-success">Save Email</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="phone_modal" tabindex="-1" role="dialog" aria-labelledby="phone_label"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form name="phones" method="post" action="/phones/create">
                    <div class="modal-header">
                        <h5 class="modal-title" id="phone_label">Add Additional Phone Numbers</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        {{ csrf_field() }}
                        <input type="hidden" name="personID" value="{{ $profile->personID }}">
                        <table id="new_phone_fields" class="table table-striped">
                            <thead>
                            <tr>
                                <th style="width: 10%">Type</th>
                                <th style="width: 20%">Phone Number</th>
                            </tr>
                            </thead>
                            <tbody>

                            @for($n=1; $n<=5; $n++)

                                <tr id="phone{{ $n }}_row"<?php if ($n > 1) echo(' style="display:none"'); ?>>
                                    <td><select name='phoneType-{{ $n }}'>
                                            <option>...</option>
                                            @include('v1.parts.form-option-show', ['array' => $phone_type])
                                        </select></td>
                                    <td><input name='phoneNumber-{{ $n }}' type='text' placeholder='Phone Number'
                                               class='form-control input-sm'>
                                </tr>

                            @endfor

                            </tbody>
                        </table>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <button type="button" id="add_prow" class="btn btn-sm btn-warning">Add Another</button>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12" style="text-align: right">
                            <button type="button" style="display: none" id="delete_prow" class="btn btn-sm btn-danger">
                                Delete
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                        <button type="submit" id="phone_submit" class="btn btn-sm btn-success">Save Phone</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
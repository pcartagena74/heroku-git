<?php
/**
 * Comment: This template is used to show a member profile --> either self or, when authorized, others
 * Created: 2/9/2017
 */

use Illuminate\Support\Facades\DB;

$currentPerson = App\Person::find(auth()->user()->id);
$string = '';
$profile_script_url = "/profile/$currentPerson->personID";
$addrURL = "/address/";
$emailURL = "/email/";
$ad_cnt = 0;  $em_cnt = 0;

if($profile->personID == $currentPerson->personID) {
    $display = "My";
} else {
    $display = $profile->firstName . " " . $profile->lastName . "'s";
}

$address_type = DB::select("select addrType as 'text', addrType as 'value' from `address-type`");
$email_type = DB::select("select emailType as 'text', emailType as 'value' from `email-type`");
$country_list = DB::select("select cntryID as 'value', cntryName as 'text' from countries");
$state_list = DB::select("select abbrev as 'text', abbrev as 'value' from state");

?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

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
            <td style="text-align: left;"><a href="#" id="prefix" data-title="Enter prefix"> {{ $profile->prefix }} </a></td>
            <td style="text-align: left;"><a data-toggle="tooltip" title="You need to contact PMI to change first name."
                   id="firstName">{{ $profile->firstName }}</a></td>
            <td style="text-align: left;"><a href="#" id="midName" data-title="Enter middle name"><?php echo($profile->midName);?></a></td>
            <td style="text-align: left;"><a data-toggle="tooltip" title="You need to contact PMI to change last name."
                   id="lastName">{{ $profile->lastName }}</a></td>
            <td style="text-align: left;"><a href="#" id="suffix" data-title="Enter suffix">{{ $profile->suffix }}</a></td>
        </tr>
        <tr>
            <th style="text-align: left;">Preferred Name</th>
            <th style="text-align: left;">Industry</th>
            <th style="text-align: left;">Company</th>
            <th style="text-align: left;">Title</th>
            <th style="text-align: left;">
                <a data-toggle="tooltip" title="If you want your login to be a new email address, you'll have to first add it by clicking 'Add Email' below.">Login</a>
            </th>
        </tr>
        <tr>
            <td style="text-align: left;"><a href="#" id="prefName" data-title="Enter preferred name">{{ $profile->prefName }}</a></td>
            <td style="text-align: left;"><a href="#" id="indName" data-title="Enter industry">{{ $profile->indName }}</a></td>
            <td style="text-align: left;"><a href="#" id="compName" data-title="Enter company name">{{ $profile->compName }}</a></td>
            <td style="text-align: left;"><a href="#" id="title" data-title="Enter title">{{ $profile->title }}</a></td>
            <td style="text-align: left;"><a href="#" id="login" data-value="{{ $profile->login }}"></a></td>
        </tr>
        </tbody>
    </table>
    @include('v1.parts.end_content')

    @if($profile->ODN1)

        @include('v1.parts.start_content', ['header' => 'Date Fields', 'subheader' => '(uneditable)', 'w1' => '4', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

        <table id='date_fields' class='table table-striped table-condensed'>
            @for($i=1;$i<=10;$i++)
                @if(isset($profile->{'ODN'.$i}))
                    <tr>
                        <td style="text-align: left;">{{ $profile->{'ODN'.$i} }}</td>
                        <td style="text-align: left;">{{ $profile->{'RelDate'.$i} }}</td>
                    </tr>
                @endif
            @endfor
        </table>

        @include('v1.parts.end_content')

    @endif

        @include('v1.parts.start_content', ['header' => 'Addresses', 'subheader' => '', 'w1' => '8', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

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
                        <form method="post" action="{{ "/address/" . $address->addrID . "/delete" }}">
                            {{ csrf_field() }}
                            <input type="hidden" name="personID" value="{{ $profile->personID }}">
                        <button class="btn btn-danger btn-xs" data-toggle="confirmation"
                                data-btn-ok-label="Continue"
                                data-btn-ok-icon="glyphicon glyphicon-share-alt"
                                data-btn-ok-class="btn-success btn-sm"
                                data-btn-cancel-label="Stop!"
                                data-btn-cancel-icon="glyphicon glyphicon-ban-circle"
                                data-btn-cancel-class="btn-danger btn-sm"
                                data-title="Are you sure?" data-content="This cannot be undone.">
                            <i class="fa fa-trash"></i>
                        </button>
                        </form>
                    </td>
                    <td><a href="#" id="addrTYPE{{ $ad_cnt }}" data-pk="{{ $address->addrID }}" data-url="{{ $addrURL . $address->addrID }}"
                           data-title="Enter address type"
                           data-value="{{ $address->addrTYPE }}">{{ $address->addrTYPE }}</a></td>
                    <td><a href="#" id="addr1{{ $ad_cnt }}" data-pk="{{ $address->addrID }}" data-url="{{ $addrURL . $address->addrID }}"
                           data-title="Enter address 1" data-value="{{ $address->addr1 }}"></a></td>
                    <td><a href="#" id="addr2{{ $ad_cnt }}" data-pk="{{ $address->addrID }}" data-url="{{ $addrURL . $address->addrID }}"
                           data-title="Enter address 2" data-value="{{ $address->addr2 }}"></a></td>
                    <td><a href="#" id="city{{ $ad_cnt }}" data-pk="{{ $address->addrID }}" data-title="Enter city" data-url="{{ $addrURL . $address->addrID }}"
                           data-value="{{ $address->city }}"></a></td>
                    <td><a href="#" id="state{{ $ad_cnt }}" data-pk="{{ $address->addrID }}" data-title="Enter state" data-url="{{ $addrURL . $address->addrID }}"
                           data-value="{{ $address->state }}"></a></td>
                    <td><a href="#" id="zip{{ $ad_cnt }}" data-pk="{{ $address->addrID }}" data-title="Enter zip code" data-url="{{ $addrURL . $address->addrID }}"
                           data-value="{{ $address->zip }}"></a></td>
                    <td><a href="#" id="cntryID{{ $ad_cnt }}" data-pk="{{ $address->addrID }}" data-url="{{ $addrURL . $address->addrID }}"
                           data-title="Enter country" data-value="{{ $address->cntryID }}"></a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="col-md-4 col-sm-9 col-xs-12">
            <button type="button" id="add_address" class="btn btn-sm btn-success"
                    data-toggle="modal" data-target="#address_modal">Add Address
            </button>
        </div>
        <div class="col-md-4 col-sm-9 col-xs-12" style="text-align: center"></div>
        <div class="col-md-4 col-sm-9 col-xs-12" style="text-align: right"></div>
        @include('v1.parts.end_content')

    @if($profile->OSN1)

        @include('v1.parts.start_content', ['header' => 'Custom Fields', 'subheader' => '(uneditable)', 'w1' => '4', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

        <table id='date_fields' class='table table-striped table-condensed'>
            @for($i=1;$i<=10;$i++)
                @if(isset($profile->{'OSN'.$i}))
                    <tr>
                        <td style="text-align: left;">{{ $profile->{'OSN'.$i} }}</td>
                        <td style="text-align: left;">{{ $profile->{'OrgStat'.$i} }}</td>
                    </tr>
                @endif
            @endfor
        </table>

        @include('v1.parts.end_content')
    @endif

    @include('v1.parts.start_content', ['header' => 'Email Addresses', 'subheader' => '', 'w1' => '4', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
    These email addresses are those that you may have used to register for an event.  <p>

    <table id="email_fields" class="table table-striped table-condensed">
        <tr>
            <th colspan="2">Type</th>
            <th>Email</th>
            <th><a data-toggle="tooltip" title="The primary address is the only one we'll use to contact you.">Primary?</a></th>
        </tr>
        @foreach($emails as $email)
            <?php $em_cnt++; ?>
            <tr>
                <td style="text-align: left;">
                    <form method="post" action="{{ "/email/" . $email->emailID . "/delete" }}">
                        {{ csrf_field() }}
                        <input type="hidden" name="personID" value="{{ $profile->personID }}">
                        <button class="btn btn-danger btn-xs" data-toggle="confirmation"
                                data-btn-ok-label="Continue"
                                data-btn-ok-icon="glyphicon glyphicon-share-alt"
                                data-btn-ok-class="btn-success btn-sm"
                                data-btn-cancel-label="Stop!"
                                data-btn-cancel-icon="glyphicon glyphicon-ban-circle"
                                data-btn-cancel-class="btn-danger btn-sm"
                                data-title="Are you sure?" data-content="This cannot be undone.">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form>
                </td>
                <td style="text-align: left;"><a href="#" id="emailTYPE{{ $em_cnt }}" data-pk="{{ $email->emailID }}" data-url="{{ $emailURL . $email->emailID }}"
                       data-title="Enter email type" data-value="{{ $email->emailTYPE }}"></a></td>
                <td style="text-align: left;"><a href="#" id="emailADDR{{ $em_cnt }}" data-pk="{{ $email->emailID }}" data-url="{{ $emailURL . $email->emailID }}"
                       data-title="Enter address 1">{{ $email->emailADDR }}</a></td>
                <td style="text-align: left;"><a href="#" id="isPrimary{{ $em_cnt }}" data-pk="{{ $email->emailID }}" data-url="{{ $emailURL . $email->emailID }}"
                       data-value="{{ $email->isPrimary }}" data-title=""></a></td>
            </tr>
        @endforeach
    </table>
    <div class="col-md-4 col-sm-9 col-xs-12">
        <button type="button" id="add_email" class="btn btn-sm btn-success" data-toggle="modal"
                data-target="#email_modal">Add Email
        </button>
    </div>
    <div class="col-md-4 col-sm-9 col-xs-12" style="text-align: center"></div>
    <div class="col-md-4 col-sm-9 col-xs-12" style="text-align: right"></div>
    @include('v1.parts.end_content')

@endsection

@section('scripts')

    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('[data-toggle="tooltip"]').tooltip({'placement': 'top'});
            //$.fn.editable.defaults.mode = 'inline';
            $.fn.editable.defaults.params = function (params) {
                params._token = $("meta[name=token]").attr("content");
                return params;
            };
            $('#prefix').editable({
                type: 'select',
                autotext: 'auto',
                pk: {{ $profile->personID }},
                url: '{{ $profile_script_url }}',
                <?php if($profile->prefix <> "") {
                    echo("value: '$profile->prefix', \n");
                } ?>
                source: [
                    <?php
                    foreach($prefixes as $row) {
                        $string .= "{ value: '" . $row->prefix . "' , text: '" . $row->prefix . "' },\n";
                    } ?>
                    {!!  rtrim($string, ",") !!}  <?php $string = ''; ?>
                ]
            });
            $('#midName').editable({
                type: 'text',
                pk: {{ $profile->personID }},
                url: '{{ $profile_script_url }}'
            });
            $('#suffix').editable({
                type: 'text',
                pk: {{ $profile->personID }},
                url: '{{ $profile_script_url }}'
            });
            $('#prefName').editable({
                type: 'text',
                pk: {{ $profile->personID }},
                url: '{{ $profile_script_url }}'
            });

            $('#indName').editable({
                type: 'select',
                autotext: 'auto',
                pk: {{ $profile->personID }},
                url: '{{ $profile_script_url }}',
                <?php if($profile->indName <> "") {
                    echo("value: '$profile->indName', \n");
                } ?>
                source: [
                    <?php
                    foreach($industries as $row) {
                        $string .= "{ value: '" . $row->industryName . "' , text: '" . $row->industryName . "' },";
                    } ?>
                    {!!  rtrim($string, ",") !!}  <?php $string = ''; ?>
                ]
            });

            $('#compName').editable({
                type: 'text',
                pk: {{ $profile->personID }},
                url: '{{ $profile_script_url }}'
            });
            $('#title').editable({
                type: 'text',
                pk: {{ $profile->personID }},
                url: '{{ $profile_script_url }}'
            });
            $('#login').editable({
                type: 'select',
                pk: {{ $profile->personID }},
                url: '{{ $profile_script_url }}',
                source: [
                @foreach($emails as $email)
                    {!! "{ value: '" . $email->emailADDR . "', text: '" . $email->emailADDR . "' }," !!}
                @endforeach
                ]
            });

            @for($j=1;$j<=$ad_cnt;$j++)
                    $('#addrTYPE{{ $j }}').editable({
                type: 'select',
                autotext: 'auto',
                source: [
                    <?php
                    foreach($addrTypes as $row) {
                        $string .= "{ value: '" . $row->addrType . "' , text: '" . $row->addrType . "' },";
                    } ?>
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
                    foreach($countries as $row) {
                        $string .= '{ value: "' . $row->cntryID . '" , text: "' . $row->cntryName . '" },';
                    } ?>
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
                    foreach($emailTypes as $row) {
                        $string .= "{ value: '" . $row->emailType . "' , text: '" . $row->emailType . "' },";
                    } ?>
                    {!!  rtrim($string, ",") !!}  <?php $string = ''; ?>
                ]
            });
            $('#emailADDR{{ $j }}').editable({type: 'text'});
            $('#isPrimary{{ $j }}').editable({
                type: 'select',
                source: [
                    {value: '0', text: 'No'},
                    {value: '1', text: 'Yes'}
                ]
            });
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

@endsection

@section('modals')
    <div class="modal fade" id="address_modal" tabindex="-1" role="dialog" aria-labelledby="address_label"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="address_label">Add Additional Addresses</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form name="addresses" method="post" action="/addresses/create">
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

                                <tr id="addr{{ $n }}_row"<?php if($n > 1) echo(' style="display:none"'); ?>>
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
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="email_modal" tabindex="-1" role="dialog" aria-labelledby="email_label"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="address_label">Add Additional Email</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form name="emails" method="post" action="/emails/create">
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

                                <tr id="email{{ $n }}_row"<?php if($n > 1) echo(' style="display:none"'); ?>>
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
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
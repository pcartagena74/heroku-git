@php
    /**
     * Comment: This template is used to show a member profile --> either self or, when authorized, others
     * Created: 2/9/2017
     *
     * @var $profile
     * @var $em_cnt
     * @var $ad_cnt
     * @var $prefixes
     *
     */

    use Illuminate\Support\Facades\DB;

    $currentPerson = App\Models\Person::find(auth()->user()->id);
    $string = '';
    $profile_script_url = env('APP_URL') . "/profile/$profile->personID";
    $op_script_url = env('APP_URL') . "/op/$profile->personID";
    $addrURL = env('APP_URL') . "/address/";
    $emailURL = env('APP_URL') . "/email/";
    $phoneURL = env('APP_URL') . "/phone/";
    $ad_cnt = 0;  $em_cnt = 0; $ph_cnt = 0;

    if(Entrust::hasRole('Admin')){
        if ($profile->personID == $currentPerson->personID) {
            $display = '<b style="color:red;">' . trans('messages.headers.your') . "</b>";
        } else {
            $display = '<b style="color:red;">' . $profile->firstName . " " . $profile->lastName . "'s</b>";
        }
    } else {
        if ($profile->personID == $currentPerson->personID) {
            $display = trans('messages.headers.my');
        } else {
            $display = '<b style="color:red;">' . $profile->firstName . " " . $profile->lastName . "'s</b>";
        }
    }

    $address_type = DB::select("select addrType as 'text', addrType as 'value' from `address-type`");
    $state_list = DB::select("select abbrev as 'text', abbrev as 'value' from state");
    $email_type = DB::select("select emailType as 'text', emailType as 'value' from `email-type`");
    $country_list = DB::select("select cntryID as 'value', cntryName as 'text' from countries");
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

@endphp
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')
    <div class="col-md-12 col-sm-12 col-xs-12">
        <ul id="myTab" class="nav nav-tabs bar_tabs nav-justified" role="tablist">
            <li class="active"><a href="#tab_content1" id="profile-tab" data-toggle="tab"
                                  aria-expanded="true"><b>@lang('messages.profile.prof_info')</b></a></li>
            <li class=""><a href="#tab_content2" id="password-tab" data-toggle="tab"
                            aria-expanded="false"><b>@lang('messages.profile.pass_mgmt')</b></a></li>
            @if(Entrust::hasRole('Speaker'))
                <li class=""><a href="#tab_content3" id="other-tab" data-toggle="tab"
                                aria-expanded="false"><b>@lang('messages.profile.spk_tab')</b></a></li>
            @endif
        </ul>

        <div id="tab-content" class="tab-content">
            <div class="tab-pane active" id="tab_content1" aria-labelledby="profile-tab">
                &nbsp;<br/>
                @if(Entrust::hasRole('Admin') || Entrust::hasRole('Developer'))
                    <div class="col-md-4 col-sm-4 col-xs-12">
                        <b>@lang('messages.headers.profile_vars.lastlog'):</b>
                        {{ $profile->lastLoginDate }}
                    </div>
                    <div class="col-md-4 col-sm-4 col-xs-12">
                        <b>@lang('messages.headers.profile_vars.created'):</b>
                        {{ $profile->createDate }}
                    </div>
                    <div class="col-md-4 col-sm-4 col-xs-12">
                        <b>@lang('messages.headers.profile_vars.updated'):</b>
                        {{ $profile->updateDate }}
                    </div>

                @endif

                @include('v1.parts.start_content', ['header' => $display . ' ' . trans('messages.profile.prof_info'),
                         'subheader' => '', 'w1' => '8', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

                <table id="profile_fields" class="table table-striped table-condensed">
                    <thead>
                    <tr>
                        <th style="width: 20%; text-align: left;">@lang('messages.fields.prefix')</th>
                        <th style="width: 20%; text-align: left;">@lang('messages.fields.firstName')</th>
                        <th style="width: 20%; text-align: left;">@lang('messages.fields.midName')</th>
                        <th style="width: 20%; text-align: left;">@lang('messages.fields.lastName')</th>
                        <th style="width: 20%; text-align: left;">@lang('messages.fields.suffix')</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td style="text-align: left;">
                            <a href="#" id="prefix"
                               data-title="{{ trans('messages.fields.prefix') }}">{{ $profile->prefix }}</a>
                        </td>
                        <td style="text-align: left;">
                            {{-- Check OrgStat1 (PMI ID) to check that PMI provided the first & last name --}}
                            @if($profile->OrgStat1 && !Entrust::hasRole('Admin'))
                                {!! $profile->firstName ?? "<i style='color:red;'>" . trans('messages.fields.empty') . "</i>" !!}
                                @include('v1.parts.tooltip', ['title' => trans('messages.instructions.name_change')])
                            @else
                                <a href="#" id="firstName" data-title="{{ trans('messages.fields.firstName') }}">
                                    {!! $profile->firstName ?? "<i style='color:red;'>" . trans('messages.fields.empty') . "</i>" !!}</a>
                                @if(Entrust::hasRole('Admin'))
                                    @include('v1.parts.tooltip', ['c' => 'red', 'title' => trans('messages.instructions.name_change_ok')])
                                @endif
                            @endif
                        </td>

                        <td style="text-align: left;">
                            <a href="#" id="midName"
                               data-title="{{ trans('messages.fields.midName') }}">{{ $profile->midName }}</a>
                        </td>
                        <td style="text-align: left;">
                            @if($profile->OrgStat1 && !Entrust::hasRole('Admin'))
                                {!! $profile->lastName ?? "<i style='color:red;'>" . trans('messages.fields.empty') . "</i>" !!}
                                @include('v1.parts.tooltip', ['title' => trans('messages.instructions.name_change')])
                            @else
                                <a href="#" id="lastName" data-title="{{ trans('messages.fields.lastName') }}">
                                    {{ $profile->lastName }}</a>
                                @if(Entrust::hasRole('Admin'))
                                    @include('v1.parts.tooltip', ['c' => 'red', 'title' => trans('messages.instructions.name_change_ok')])
                                @endif
                            @endif
                        </td>
                        <td style="text-align: left;">
                            <a href="#" id="suffix"
                               data-title="{{ trans('messages.fields.suffix') }}">{{ $profile->suffix }}</a>
                        </td>
                    </tr>
                    <tr>
                        <th style="text-align: left;">@lang('messages.fields.prefName')</th>
                        <th style="text-align: left;">@lang('messages.fields.indName')</th>
                        <th style="text-align: left;">@lang('messages.fields.compName')</th>
                        <th style="text-align: left;">@lang('messages.fields.title')</th>
                        <th style="text-align: left;">
                            @lang('messages.buttons.login')
                            @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.login')])
                        </th>
                    </tr>
                    <tr>
                        <td style="text-align: left;"><a href="#" id="prefName"
                                                         data-title="{{ trans('messages.fields.prefName') }}">{{ $profile->prefName }}</a>
                        </td>
                        <td style="text-align: left;"><a href="#" id="indName"
                                                         data-title="{{ trans('messages.fields.indName') }}">{{ $profile->indName }}</a>
                        </td>
                        <td style="text-align: left;"><a href="#" id="compName"
                                                         data-title="{{ trans('messages.fields.compName') }}">{{ $profile->compName }}</a>
                        </td>
                        <td style="text-align: left;"><a href="#" id="title"
                                                         data-title="{{ trans('messages.profile.directions.title') }}">{{ $profile->title }}</a>
                        </td>
                        <td style="text-align: left;"><a href="#" id="login" data-value="{{ $profile->login }}"></a>
                        </td>
                    </tr>
                    <tr>
                        <th style="text-align: left;">@lang('messages.fields.experience')</th>
                        <th style="text-align: left;">@lang('messages.headers.chapterRole')</th>
                        <th style="text-align: left;">@lang('messages.headers.affiliation')</th>
                        <th style="text-align: left;">@lang('messages.fields.diet_res')</th>
                        <th style="text-align: left;">@lang('messages.fields.diet_com')</th>
                    </tr>
                    <tr>
                        <td style="text-align: left;">
                            <a href="#" id="experience"
                               data-title="{{ trans('messages.fields.experience') }} ({{ trans('messages.fields.years') }})">
                                {{ $profile->experience }}</a>
                        </td>
                        <td style="text-align: left;">
                            <a href="#" id="chapterRole" data-title="{{ trans('messages.headers.chapterRole') }}">
                                {{ $profile->chapterRole }}</a>
                        </td>
                        <td style="text-align: left;">
                            <a href="#" id="affiliation" data-title="{{ trans('messages.fields.affiliation') }}">
                                {{ $profile->affiliation }}</a>
                        </td>
                        <td style="text-align: left;"><a href="#" id="allergenInfo"
                                                         data-title="{{ trans('messages.fields.diet_res') }}">
                                {{ $profile->allergenInfo }}</a>
                        </td>
                        <td style="text-align: left;"><a href="#" id="allergenNote"
                                                         data-title="{{ trans('messages.fields.diet_com') }}">
                                {{ $profile->allergenNote }}</a>
                        </td>
                    </tr>
                    <tr>
                        {{-- Adding new fields to person for profile display will require the update of the show() query --}}
                        <th style="text-align: left;">@lang('messages.headers.twitter')</th>
                        <th style="text-align: left;">@lang('messages.headers.certs')</th>
                    </tr>
                    <tr>
                        <td style="text-align: left;"><a href="#" id="twitterHandle" data-title="">
                                {{ $profile->twitterHandle }}</a>
                        </td>
                        <td style="text-align: left;"><a href="#" id="certifications" data-title="">
                                {{ $profile->certifications }}</a>
                        </td>
                    </tr>
                    </tbody>
                </table>
                @include('v1.parts.end_content')


                @if(Entrust::hasRole('Admin'))
                    @include('v1.parts.start_content', ['header' => trans('messages.profile.date_f'),
                             'subheader' => '<b class="red">(' . trans('messages.profile.ed_ad') . ')</b>',
                             'w1' => '4', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
                    <table id='date_fields' class='table table-striped table-condensed'>
                        @for($i=1;$i<=10;$i++)
                            @if(isset($profile->{'ODN'.$i}))
                                <tr>
                                    <td style="text-align: left;">{{ $profile->{'ODN'.$i} }}</td>
                                    <td style="text-align: left;">
                                        <a href="#" id="RelDate{{$i}}"
                                           data-value="{!! $profile->{'RelDate'.$i} !!}"></a>
                                    </td>
                                </tr>
                            @elseif($i == 1)
                                @lang('messages.profile.no_id')
                            @endif
                        @endfor
                    </table>
                    @include('v1.parts.end_content')
                @else
                    @include('v1.parts.start_content', ['header' => trans('messages.profile.date_f'),
                             'subheader' => '(' . trans('messages.profile.uneditable') . ')',
                             'w1' => '4', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
                    <table id='date_fields' class='table table-striped table-condensed'>
                        @for($i=1;$i<=10;$i++)
                            @if(isset($profile->{'ODN'.$i}))
                                <tr>
                                    <td style="text-align: left;">{{ $profile->{'ODN'.$i} }}</td>
                                    <td style="text-align: left;">{!! $profile->{'RelDate'.$i} ?? "<i style='color:red;'>" . trans('messages.fields.empty') . "</i>" !!}</td>
                                </tr>
                            @elseif($i == 1)
                                @lang('messages.profile.no_id')
                            @endif
                        @endfor
                    </table>
                    @include('v1.parts.end_content')
                @endif

                {{-- Email Addresses Go Here --}}
                @include('v1.parts.start_content', ['header' => trans('messages.fields.email'),
                         'subheader' => '', 'w1' => '8', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
                @if(count($emails) == 0)
                    {{ trans_choice('messages.profile.emails', count($emails)) }}
                @else
                    {{ trans_choice('messages.profile.emails', count($emails)) }}

                    <table id="email_fields" class="table table-striped table-condensed">
                        <tr>
                            <th style="text-align:center;" colspan="2">@lang('messages.profile.type')</th>
                            <th style="text-align:left;">@lang('messages.headers.email')</th>
                            <th style="text-align:left;">
                                @lang('messages.headers.primary')?
                                @include('v1.parts.tooltip', ['title' => trans('messages.profile.primary')])
                            </th>
                        </tr>
                        @foreach($emails as $email)
                            @php $em_cnt++; @endphp
                            <tr>
                                <td style="text-align: left;">
                                    @if($email->isPrimary)
                                        <button class="btn btn-danger btn-xs"
                                                disabled>@lang('messages.symbols.trash')</button>
                                        @include('v1.parts.tooltip', ['title' => trans('messages.profile.cant_delete')])
                                    @else
                                        <form method="post"
                                              action="{{ env('APP_URL') . "/email/" . $email->emailID . "/delete" }}">
                                            {{ csrf_field() }}
                                            <input type="hidden" name="personID" value="{{ $profile->personID }}">
                                            <button class="btn btn-danger btn-xs" data-toggle="tooltip"
                                                    title="{{ trans('messages.tooltips.delete') }}"
                                                    onclick="return confirm('{{ trans('messages.tooltips.sure_del') }}');">
                                                @lang('messages.symbols.trash')
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
                                                                 data-title="{{ trans('messages.profile.addr1') }}">{{ $email->emailADDR }}</a>
                                </td>
                                <td style="text-align: left;">@if($email->isPrimary)
                                        @lang('messages.yesno_check.yes')
                                    @else
                                        @lang('messages.yesno_check.no')
                                    @endif

                                </td>
                            </tr>
                        @endforeach
                    </table>
                @endif
                <div class="col-md-4 col-sm-9 col-xs-12">
                    <button type="button" id="add_email" class="btn btn-sm btn-success" data-toggle="modal"
                            data-target="#email_modal">@lang('messages.profile.add_email')
                    </button>
                </div>
                <p>&nbsp;</p>
                @include('v1.parts.end_content')

                {{-- Custom Chapter Data --}}
                @if(Entrust::hasRole('Admin'))
                    @include('v1.parts.start_content', ['header' => trans('messages.profile.custom'),
                             'subheader' => '<b class="red">(' . trans('messages.profile.ed_ad') . ')</b>',
                             'w1' => '4', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
                    <table id='date_fields' class='table table-striped table-condensed'>
                        @for($i=1;$i<=10;$i++)
                            @if(isset($profile->{'OSN'.$i}))
                                <tr>
                                    <td style="text-align: left;">{{ $profile->{'OSN'.$i} }}</td>
                                    <td style="text-align: left;">
                                        <a href="#" id="OrgStat{{$i}}"
                                           data-value="{!! $profile->{'OrgStat'.$i} !!}"></a>
                                    </td>
                                </tr>
                            @elseif($i == 1)
                                @lang('messages.profile.no_id')
                            @endif
                        @endfor
                    </table>
                    @include('v1.parts.end_content')
                @else
                    @include('v1.parts.start_content', ['header' => trans('messages.profile.custom'),
                             'subheader' => '(' . trans('messages.profile.uneditable') . ')', 'w1' => '4', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
                    <table id='date_fields' class='table table-striped table-condensed'>
                        @for($i=1;$i<=10;$i++)
                            @if(isset($profile->{'OSN'.$i}))
                                <tr>
                                    <td style="text-align: left;">{{ $profile->{'OSN'.$i} }}</td>
                                    <td style="text-align: left;">{!! $profile->{'OrgStat'.$i} ?? "<i style='color:red;'>" . trans('messages.fields.empty') . "</i>" !!}</td>
                                </tr>
                            @elseif($i == 1)
                                @lang('messages.profile.no_id')
                            @endif
                        @endfor
                    </table>
                    @include('v1.parts.end_content')
                @endif

                {{--  Address Goes Here --}}
                @include('v1.parts.start_min_content', ['header' => trans_choice('messages.profile.addr', 2),
                         'subheader' => '', 'w1' => '8', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

                @if(count($addresses) == 0)
                    {{ trans_choice('messages.profile.addrs', 1) }}
                @else
                    <table id="address_fields" class="table table-striped table-condensed">
                        <thead>
                        <tr>
                            <th></th>
                            <th style="width: 10%">@lang('messages.profile.type')</th>
                            <th style="width: 20%">@lang('messages.profile.addr1')</th>
                            <th style="width: 20%">@lang('messages.profile.addr2')</th>
                            <th style="width: 20%">@lang('messages.profile.city')</th>
                            <th style="width: 10%">@lang('messages.profile.state')</th>
                            <th style="width: 10%">@lang('messages.profile.zip')</th>
                            <th style="width: 10%">@lang('messages.profile.country')</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($addresses as $address)
                            @php $ad_cnt++; @endphp
                            <tr>
                                <td>
                                    <form id="ad-{{ $ad_cnt }}" method="post"
                                          action="{{ env('APP_URL') . "/address/" . $address->addrID . "/delete" }}">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="personID" value="{{ $profile->personID }}">
                                        <button class="btn btn-danger btn-xs" data-toggle="tooltip"
                                                title="{{ trans('messages.tooltips.delete') }}"
                                                onclick="return confirm('{{ trans('messages.tooltips.sure_del') }}');">
                                            @lang('messages.symbols.trash')
                                        </button>
                                    </form>
                                </td>
                                <td><a href="#" id="addrTYPE{{ $ad_cnt }}" data-pk="{{ $address->addrID }}"
                                       data-url="{{ $addrURL . $address->addrID }}"
                                       data-title="{{ trans('messages.profile.type') }}"
                                       data-value="{{ $address->addrTYPE }}">{{ $address->addrTYPE }}</a></td>
                                <td><a href="#" id="addr1{{ $ad_cnt }}" data-pk="{{ $address->addrID }}"
                                       data-url="{{ $addrURL . $address->addrID }}"
                                       data-title="{{ trans('messages.profile.addr1') }}"
                                       data-value="{{ $address->addr1 }}"></a></td>
                                <td><a href="#" id="addr2{{ $ad_cnt }}" data-pk="{{ $address->addrID }}"
                                       data-url="{{ $addrURL . $address->addrID }}"
                                       data-title="{{ trans('messages.profile.addr2') }}"
                                       data-value="{{ $address->addr2 }}"></a></td>
                                <td><a href="#" id="city{{ $ad_cnt }}" data-pk="{{ $address->addrID }}"
                                       data-title="{{ trans('messages.profile.city') }}"
                                       data-url="{{ $addrURL . $address->addrID }}"
                                       data-value="{{ $address->city }}"></a></td>
                                <td><a href="#" id="state{{ $ad_cnt }}" data-pk="{{ $address->addrID }}"
                                       data-title="{{ trans('messages.profile.state') }}"
                                       data-url="{{ $addrURL . $address->addrID }}"
                                       data-value="{{ $address->state }}"></a></td>
                                <td><a href="#" id="zip{{ $ad_cnt }}" data-pk="{{ $address->addrID }}"
                                       data-title="{{ trans('messages.profile.zip') }}"
                                       data-url="{{ $addrURL . $address->addrID }}"
                                       data-value="{{ $address->zip }}"></a></td>
                                <td><a href="#" id="cntryID{{ $ad_cnt }}" data-pk="{{ $address->addrID }}"
                                       data-url="{{ $addrURL . $address->addrID }}"
                                       data-title="{{ trans('messages.profile.country') }}"
                                       data-value="{{ $address->cntryID }}"></a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
                <div class="col-md-4 col-sm-9 col-xs-12">
                    <button type="button" id="add_address" class="btn btn-sm btn-success"
                            data-toggle="modal" data-target="#address_modal"> @lang('messages.profile.add_addr')
                    </button>
                </div>
                @include('v1.parts.end_content')


                {{-- Phone Number Section Goes Here --}}
                @include('v1.parts.start_min_content', ['header' => trans_choice('messages.headers.phone_nums', 2),
                         'subheader' => '', 'w1' => '4', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

                @if(count($phones) == 0)
                    @lang('messages.profile.no_phone')
                @else

                    <table id="phone_fields" class="table table-striped table-condensed">
                        <tr>
                            <th style="text-align:center;" colspan="2">@lang('messages.profile.type')</th>
                            <th style="text-align:left;">{{ trans_choice('messages.headers.phone_nums', 1) }}</th>
                        </tr>

                        @foreach($phones as $phone)
                                <?php $ph_cnt++; ?>
                            <tr>
                                <td style="text-align: left;">
                                    <form id="ph-{{ $ph_cnt }}" method="post"
                                          action="{{ env('APP_URL') . "/phone/" . $phone->phoneID . "/delete" }}">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="personID" value="{{ $profile->personID }}">
                                        <button class="btn btn-danger btn-xs" data-toggle="tooltip"
                                                title="{{ trans('messages.tooltips.delete') }}"
                                                onclick="return confirm('{{ trans('messages.tooltips.sure') }}');">
                                            @lang('messages.symbols.trash')
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
                            data-target="#phone_modal">@lang('messages.profile.add_number')
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
                        @include('v1.parts.start_content', ['header' => trans('messages.profile.change') . $display . trans('messages.profile.pass'),
                            'subheader' => '', 'w1' => '8', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

                        {!! Form::open(array('url' => env('APP_URL')."/force_password", 'method' => 'POST')) !!}
                        <div class="form-group">
                            {!! Form::label('userid', trans('messages.headers.userid'), array('class' => 'control-label')) !!}
                            {!! Form::number('userid', $profile->personID, $attributes = array('class' => 'form-control', 'required', 'readonly')) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('newPass', trans('messages.headers.pass_new'), array('class' => 'control-label')) !!}
                            {!! Form::password('password', $attributes = array('class' => 'form-control', 'required')) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('password_confirmation', trans('messages.headers.pass_ver'), array('class' => 'control-label')) !!}
                            {!! Form::password('password_confirmation', $attributes = array('class' => 'form-control', 'required')) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::submit(trans('messages.nav.m_pass'), array('class' => 'btn btn-primary btn-sm')) !!}
                        </div>
                        {!! Form::close() !!}

                        @include('v1.parts.end_content')
                    @else
                        {!! Form::open(array('url' => env('APP_URL')."/password", 'method' => 'POST')) !!}
                        <div class="form-group">
                            {!! Form::label('curPass', trans('messages.profile.pass_cur'), array('class' => 'control-label')) !!}
                            {!! Form::password('curPass', $attributes = array('class' => 'form-control', 'required')) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('newPass', trans('messages.headers.pass_new'), array('class' => 'control-label')) !!}
                            {!! Form::password('password', $attributes = array('class' => 'form-control', 'required')) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('password_confirmation', trans('messages.headers.pass_ver'), array('class' => 'control-label')) !!}
                            {!! Form::password('password_confirmation', $attributes = array('class' => 'form-control', 'required')) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::submit(trans('messages.profile.change') . trans('messages.profile.pass'), array('class' => 'btn btn-primary btn-sm')) !!}
                        </div>
                        {{-- current, new, verify --}}
                        {!! Form::close() !!}
                    @endif

                </div>
            </div>
            @if(Entrust::hasRole('Speaker'))
                <div class="tab-pane fade" id="tab_content3" aria-labelledby="other-tab">
                    &nbsp;<br/>
                    <b>@lang('messages.profile.spk_info')</b>
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
        @include('v1.parts.menu-fix', array('path' => '/members', 'tag' => '#mem', 'newTxt' => trans('messages.nav.ms_edit')))
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
                @php
                    if ($profile->experience <> "") {
                        echo("value: '$profile->experience',\n");
                    }
                @endphp
                source: [
                    {value: '1-4', text: '1-4 {{ trans('messages.fields.years') }}'},
                    {value: '5-9', text: '5-9 {{ trans('messages.fields.years') }}'},
                    {value: '10-14', text: '10-14 {{ trans('messages.fields.years') }}'},
                    {value: '15-19', text: '15-19 {{ trans('messages.fields.years') }}'},
                    {value: '20+', text: '20+ {{ trans('messages.fields.years') }}'}
                ]
            });

            $('#prefix').editable({
                type: 'select',
                autotext: 'auto',
                pk: '{{ $profile->personID }}',
                url: '{{ $profile_script_url }}',
                @php
                    if ($profile->prefix <> "") {
                        echo("value: '$profile->prefix', \n");
                    }
                @endphp
                source: [
                    @php
                        foreach ($prefixes as $k => $i) {
                            $string .= "{ value: '" . $k . "' , text: '" . $i . "' },\n";
                        }
                    @endphp
                            {!!  rtrim($string, ",") !!}  <?php $string = ''; ?>
                ]
            });
            @if(!$profile->OrgStat1 || Entrust::hasRole('Admin'))
            $('#firstName').editable({
                type: 'text',
                maxlength: 50,
                pk: '{{ $profile->personID }}',
                url: '{{ $profile_script_url }}'
            });
            $('#lastName').editable({
                type: 'text',
                maxlength: 50,
                pk: '{{ $profile->personID }}',
                url: '{{ $profile_script_url }}'
            });
            @endif

            $('#midName').editable({
                type: 'text',
                maxlength: 50,
                pk: '{{ $profile->personID }}',
                url: '{{ $profile_script_url }}'
            });

            $('#suffix').editable({
                type: 'text',
                maxlength: 10,
                pk: '{{ $profile->personID }}',
                url: '{{ $profile_script_url }}'
            });
            $('#prefName').editable({
                type: 'text',
                maxlength: 50,
                pk: '{{ $profile->personID }}',
                url: '{{ $profile_script_url }}'
            });

            $('#indName').editable({
                type: 'select',
                maxlength: 100,
                autotext: 'auto',
                pk: '{{ $profile->personID }}',
                url: '{{ $profile_script_url }}',
                @php
                    if ($profile->indName <> "") {
                        echo("value: '$profile->indName', \n");
                    }
                @endphp
                source: [
                    @php
                        foreach ($industries as $k => $i) {
                            $string .= "{ value: '" . $k . "' , text: '" . $i . "' },";
                        }
                    @endphp
                            {!!  rtrim($string, ",") !!}  <?php $string = ''; ?>
                ]
            });

            $('#compName').editable({
                type: 'text',
                maxlength: 255,
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
                maxlength: 100,
                pk: '{{ $profile->personID }}',
                url: '{{ $op_script_url }}'
            });

            $('#twitterHandle').editable({
                type: 'text',
                maxlength: 50,
                pk: '{{ $profile->personID }}',
                url: '{{ $profile_script_url }}'
            });

            $('#affiliation').editable({
                type: 'checklist',
                pk: '{{ $profile->personID }}',
                url: '{{ $profile_script_url }}',
                value: '{{ $profile->affiliation }}',
                source: [
                    @php
                        for ($j = 1; $j <= count($affiliation_array); $j++) {
                            $string .= "{ value: '" . $affiliation_array[$j] . "' , text: '" . $affiliation_array[$j] . "' },";
                        }
                    @endphp
                            {!!  rtrim($string, ",") !!}  <?php $string = ''; ?>
                ]
            });

            $("#certifications").editable({
                type: 'checklist',
                pk: '{{ $profile->personID }}',
                url: '{{ $profile_script_url }}',
                value: '{{ $profile->certifications }}',
                source: [
                    @php
                        foreach ($cert_array as $x) {
                            $string .= "{ value: '" . $x->certification . "' , text: '" . $x->certification . "' },";
                        }
                    @endphp
                            {!!  rtrim($string, ",") !!}  <?php $string = ''; ?>
                ],
            });

            $("#allergenInfo").editable({
                type: 'checklist',
                pk: '{{ $profile->personID }}',
                url: '{{ $profile_script_url }}',
                source: [
                    @php
                        foreach ($allergen_array as $x) {
                            $string .= "{ value: '" . $x . "' , text: '" . $x . "' },";
                        }
                    @endphp
                            {!!  rtrim($string, ",") !!}  <?php $string = ''; ?>
                ]
            });

            $('#allergenNote').editable({
                type: 'text',
                maxlength: 255,
                pk: '{{ $profile->personID }}',
                url: '{{ $profile_script_url }}'
            });

            @for($j=1;$j<=$ad_cnt;$j++)
            $('#addrTYPE{{ $j }}').editable({
                type: 'select',
                autotext: 'auto',
                source: [
                    @php
                        foreach ($addrTypes as $row) {
                            $string .= "{ value: '" . $row->addrType . "' , text: '" . $row->addrType . "' },";
                        }
                    @endphp
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
                    @php
                        foreach ($countries as $row) {
                            $string .= '{ value: "' . $row->cntryID . '" , text: "' . $row->cntryName . '" },';
                        }
                    @endphp
                            {!!  rtrim($string, ",") !!}  @php $string = ''; @endphp
                ]
            });
            @endfor

            @for($j=1;$j<=$em_cnt;$j++)
            $('#emailTYPE{{ $j }}').editable({
                type: 'select',
                autotext: 'auto',
                source: [
                    @php
                        foreach ($emailTypes as $row) {
                            $string .= "{ value: '" . $row->emailType . "' , text: '" . $row->emailType . "' },";
                        }
                    @endphp
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
                    @php
                        foreach ($phoneTypes as $row) {
                            $string .= "{ value: '" . $row->phoneType . "' , text: '" . $row->phoneType . "' },";
                        }
                    @endphp
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
                    $('#addrTYPE-' + i).required = true;
                    $('#addr1-' + i).required = true;
                    $('#addr2-' + i).required = true;
                    $('#city-' + i).required = true;
                    $('#state-' + i).required = true;
                    $('#zip-' + i).required = true;
                    $('#cntryID-' + i).required = true;
                    i++;
                }
                if (i >= 3) {
                    $('#addr_submit').text("{{ trans_choice('messages.buttons.save_ad', 2) }}");
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
                    $('#addrTYPE-' + i).removeAttr('required');
                    $('#addr1-' + i).removeAttr('required');
                    $('#addr2-' + i).removeAttr('required');
                    $('#city-' + i).removeAttr('required');
                    $('#state-' + i).removeAttr('required');
                    $('#zip-' + i).removeAttr('required');
                    $('#cntryID-' + i).removeAttr('required');
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
                    $('#emailTYPE-' + i).required = true;
                    $('#emailADDR-' + i).required = true;
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
                    $('#emailTYPE-' + i).removeAttr('required');
                    $('#emailADDR-' + i).removeAttr('required');
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
                    $('#phoneTYPE-' + i).required = true;
                    $('#phoneNumber-' + i).required = true;
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
                    $('#phoneTYPE-' + i).removeAttr('required');
                    $('#phoneNumber-' + i).removeAttr('required');
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
    @include('v1.modals.context_sensitive_issue')
    <div class="modal fade" id="address_modal" tabindex="-1" role="dialog" aria-labelledby="address_label"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form name="addresses" method="post" action="/addresses/create">
                    <div class="modal-header">
                        <h5 class="modal-title" id="address_label">@lang('messages.profile.add_addr')</h5>
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
                                <th style="width: 10%">@lang('messages.profile.type')</th>
                                <th style="width: 20%">@lang('messages.profile.addr1&2')</th>
                                <th style="width: 20%">@lang('messages.profile.city')</th>
                                <th style="width: 10%">@lang('messages.profile.state')</th>
                                <th style="width: 10%">@lang('messages.profile.zip')</th>
                                <th style="width: 10%">@lang('messages.profile.country')</th>
                            </tr>
                            </thead>
                            <tbody>

                            @for($n=1; $n<=5; $n++)

                                <tr id="addr{{ $n }}_row"{!! $n>1 ? " style='display:none'" : "" !!}>
                                    <td><select name='addrTYPE-{{ $n }}'{!! $n == 1 ? " required" : "" !!}>
                                            <option value="">...</option>
                                            @include('v1.parts.form-option-show', ['array' => $address_type])
                                        </select></td>
                                    <td><input name='addr1-{{ $n }}' type='text'
                                               placeholder='{{ trans('messages.profile.addr1') }}'
                                               class='form-control input-sm'{!! $n == 1 ? " required" : "" !!}>
                                        <input name='addr2-{{ $n }}' type='text'
                                               placeholder='{{ trans('messages.profile.addr2') }}'
                                               class='form-control input-sm'{!! $n == 1 ? " required" : "" !!}></td>
                                    <td><input name='city-{{ $n }}' type='text'
                                               placeholder='{{ trans('messages.profile.city') }}'
                                               class='form-control input-sm'{!! $n == 1 ? " required" : "" !!}></td>
                                    <td><select name='state-{{ $n }}'{!! $n == 1 ? " required" : "" !!}>
                                            <option value="">...</option>
                                            @include('v1.parts.form-option-show', ['array' => $state_list])
                                            <td><input name='zip-{{ $n }}' type='text' size="5"
                                                       placeholder='{{ trans('messages.profile.zip') }}'
                                                       style="width: 65px"
                                                       class='form-control input-sm'{!! $n == 1 ? " required" : "" !!}>
                                            </td>
                                            <td><select name='cntryID-{{ $n }}'
                                                        style="width: 50px"{!! $n == 1 ? " required" : "" !!}>
                                                    <option value="">...</option>
                                                    @include('v1.parts.form-option-show', ['array' => $country_list])
                                                </select></td>
                                </tr>
                            @endfor
                            </tbody>
                        </table>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <button type="button" id="add_row"
                                    class="btn btn-sm btn-warning">@lang('messages.buttons.another')</button>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12" style="text-align: right">
                            <button type="button" style="display: none" id="delete_row" class="btn btn-sm btn-danger">
                                @lang('messages.buttons.delete')
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm"
                                data-dismiss="modal">@lang('messages.buttons.close')</button>
                        <button type="submit" id="addr_submit"
                                class="btn btn-sm btn-success">{{ trans_choice('messages.buttons.save_ad', 1) }}</button>
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
                        <h5 class="modal-title" id="address_label">@lang('messages.profile.add_email')</h5>
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
                                <th style="width: 10%">@lang('messages.profile.type')</th>
                                <th style="width: 20%">@lang('messages.fields.email')</th>
                            </tr>
                            </thead>
                            <tbody>

                            @for($n=1; $n<=5; $n++)

                                <tr id="email{{ $n }}_row"{!! $n>1 ? ' style="display:none"' : '' !!}>
                                    <td><select name='emailTYPE-{{ $n }}'>
                                            <option value="">...</option>
                                            @include('v1.parts.form-option-show', ['array' => $address_type])
                                        </select></td>
                                    <td><input name='emailADDR-{{ $n }}' type='email'
                                               placeholder='{{ trans('messages.fields.email') }}'
                                               class='form-control input-sm'>
                                </tr>

                            @endfor

                            </tbody>
                        </table>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <button type="button" id="add_erow"
                                    class="btn btn-sm btn-warning">@lang('messages.buttons.another')</button>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12" style="text-align: right">
                            <button type="button" style="display: none" id="delete_erow" class="btn btn-sm btn-danger">
                                @lang('messages.buttons.delete')
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm"
                                data-dismiss="modal">@lang('messages.buttons.close')</button>
                        <button type="submit" id="email_submit"
                                class="btn btn-sm btn-success">{{ trans_choice('messages.buttons.save_em', 1) }}</button>
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

                                <tr id="phone{{ $n }}_row"{!!$n>1 ? ' style="display:none"' : '' !!}>
                                    <td><select name='phoneType-{{ $n }}'>
                                            <option value="">...</option>
                                            @include('v1.parts.form-option-show', ['array' => $phone_type])
                                        </select></td>
                                    <td><input name='phoneNumber-{{ $n }}'
                                               placeholder='{{ trans_choice('messages.headers.phone_nums', 1) }}'
                                               type='text' class='form-control input-sm'>
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
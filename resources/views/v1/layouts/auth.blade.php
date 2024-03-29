@php
/**
 * Comment: Template for pages that require authentication
 * Created: 2/2/2017
 */

use App\Models\Person;

// footer_fixed
if(!isset($topBits)){
    $topBits = '';
}

// This is a reminder to NOT perform tests using orgID=1 because there are checks that are performed to keep default
// data created in orgID=1--for use with all other orgs--in a clean state.
if(auth()){
    $p = Person::find(auth()->user()->id);
    if($p->defaultOrgID==1){
        request()->session()->flash('alert-danger', trans('messages.app_defaults.orgID1'));
    }
}
@endphp
<!DOCTYPE html>
@include('v1.parts.header')
@yield('header')
</head>
<body class="nav-md footer_fixed">
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-K4MGRCX"
                  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<div class="container body">
    <div class="main_container">
        @include('v1.parts.nav-left')
        @include('v1.parts.nav-top')
        <div class="right_col" role="main">
            @if(Session::has('become'))
                @include('v1.parts.become_notice')
            @endif
                @include('v1.parts.error')
            @if($topBits)
                <div class="row tile_count hidden-xs hidden-sm">
                    @foreach($topBits as $tdata)
                        @if(count($tdata)>6)
                            @include('v1.parts.title-bit', ['icon' => $tdata[0], 'label' => $tdata[1],
                            'number' => $tdata[2], 'ctext'=> $tdata[3], 'rtext' => $tdata[4], 'up' => $tdata[5], 'width' => $tdata[6]])
                        @else
                            @include('v1.parts.title-bit', ['icon' => $tdata[0], 'label' => $tdata[1],
                            'number' => $tdata[2], 'ctext'=> $tdata[3], 'rtext' => $tdata[4], 'up' => $tdata[5]])
                        @endif
                    @endforeach
                </div>
            @endif

            @yield('content')

            <p class="clearfix">&nbsp;</p>
            @include('v1.parts.footer')
        </div>
    </div>
</div>
@include('v1.parts.footer_script')
<script>
    $("[data-toggle=tooltip]").tooltip();
</script>
@yield('scripts')
@yield('footer')
@yield('modals')
</body>
</html>
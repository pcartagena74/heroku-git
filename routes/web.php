<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*
  // This is to debug by seeing eloquent --> sql
\Event::listen('Illuminate\Database\Events\QueryExecuted', function ($query) {
    var_dump($query->sql);
    var_dump($query->bindings);
    var_dump($query->time);
});
// */
/*
Route::get('/linkedin1', 'SocialController@linkedin_login');

Route::get('/linkedin2', function()
{
    $data = Session::get('data');
    //return View::make('user')->with('data', $data);
    $topBits = '';
    return view('v1.auth_pages.members.linkedin', compact('data', 'topBits'));
});
*/

// Public Routes
Route::get('/', 'HomeController@index')->name('home');

Route::post('/login', 'Auth\LoginController@login')->name('login');
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');

Route::get('/policies', function () {
    return view('v1.public_pages.policies');
});
Route::get('/pricing', function () {
    return view('v1.public_pages.pricing');
});
Route::get('/details', function () {
    return view('v1.public_pages.details');
});
Route::get('/mktg', function () {
    return view('v1.public_pages.details');
})->name('mktg');
Route::get('/mail', function () {
    return view('v1.public_pages.details');
})->name('mail');
Route::get('/mtgs', function () {
    return view('v1.public_pages.details');
})->name('mtgs');

Route::get('/pmi_lookup/{org}', 'OrgPersonController@index');
Route::post('/pmi_lookup', 'OrgPersonController@find');
Route::get('/pmi_account/{person}', 'OrgPersonController@show');


Route::get('/password/resetmodal', 'Auth\ResetPasswordController@showResetForm_inModal');
Route::get('/password/forgotmodal', 'Auth\ForgotPasswordController@showLinkRequestForm_inModal');

// Public Event-related Routes
Route::get('/events/{eventslug}/{override?}', 'EventController@show')->name('display_event');
Route::post('/discount/{event}', 'EventDiscountController@showDiscount')->name('check_discount');    // Ajax
Route::post('/eLookup/{email}', 'EmailController@show')->name('lookup_email');                       // Ajax
Route::post('/oLookup/{pmiid}', 'PublicFunctionController@oLookup')->name('lookup_pmiid');           // Ajax

// Public Session-related Routes
Route::get('/rs/{session}', 'RegSessionController@show')->name('self_checkin');
Route::post('/rs/{session}/edit', 'RegSessionController@store_session');
Route::get('/rs_survey/{rs}', 'RegSessionController@show_session_survey');
Route::post('/rs_survey', 'RegSessionController@store_survey');
Route::get('/mail_surveys/{event}', 'RegSessionController@send_surveys');

Route::get('/checkin/{event}/{session?}', 'RegSessionController@volunteer_checkin');
Route::post('/process_checkin', 'RegSessionController@process_checkin');

Route::get('/storage/events/{filename}', function ($filename) {
    $filePath = Flysystem::connection('awss3')->get($filename);
    return redirect($filePath);
});

// Individual Public Page Routes
// -----------------------------
// Dashboard or Regular User "Home"
Route::get('/dashboard', 'ActivityController@index')->name('dashboard');
Route::post('/networking', 'ActivityController@networking');                                               // Ajax
Route::get('/home', 'ActivityController@index');
Route::get('/upcoming', 'ActivityController@future_index')->name('upcoming_events');
Route::post('/update_sessions/{reg}', 'RegSessionController@update_sessions')->name('update_sessions');
Route::post('/event_checkin/{event}', 'RegSessionController@store')->name('default_sess_checkin');


// Private Admin Page Routes
// -------------------------
Route::get('/newuser/create', 'UserController@create');
Route::post('/newuser', 'UserController@store');
Route::get('/become', 'ActivityController@create');
Route::post('/become', 'ActivityController@become');

// My Profile / Member Editing
// ---------------------
// Linked in routes need to be above profile routes so "linkedin" overrides {id}
Route::get('/profile/linkedin', 'PersonController@redirectToLinkedIn');
Route::get('/profile/linkedin/callback', 'PersonController@handleLinkedInCallback');

Route::get('/profile/{id}/{modal?}', 'PersonController@show')->name('showMemberProfile');
Route::post('/profile/{id}', 'PersonController@update');                                                   // Ajax
Route::post('/op/{id}', 'PersonController@update_op');                                                     // Ajax
Route::post('/address/{id}', 'AddressController@update');
Route::post('/addresses/create', 'AddressController@store');
Route::post('/locations/create', 'LocationController@store');
Route::post('/address/{id}/delete', 'AddressController@destroy');
Route::post('/email/{id}', 'EmailController@update');
Route::post('/emails/create', 'EmailController@store');
Route::post('/email/{id}/delete', 'EmailController@destroy');
Route::post('/phone/{id}', 'PhoneController@update');
Route::post('/phones/create', 'PhoneController@store');
Route::post('/phone/{id}/delete', 'PhoneController@destroy');
Route::post('/password', 'PersonController@change_password');
Route::get('/force', 'PersonController@show_force');
Route::post('/force_password', 'PersonController@force_password_change');


Route::get('/u/{person}/{email}', 'PersonController@undo_login')->name('UndoLogin');

// Organizational Routes
// ---------------------
// Settings
Route::get('/orgsettings', 'OrgController@index');
Route::get('/orgsettings/{id}', 'OrgController@show');
Route::post('/orgsettings/{id}', 'OrgController@update');                                                  // Ajax
Route::get('/eventdefaults', 'OrgController@event_defaults');
Route::post('/orgdiscounts/{id}', 'OrgDiscountController@update');                                         // Ajax

Route::get('/load_data', 'UploadController@index');
Route::post('/load_data', 'UploadController@store');

Route::get('/role_mgmt/{query?}', 'RoleController@index');
Route::post('/role_search', 'RoleController@search');
Route::post('/role/{person}/{role}', 'RoleController@update');                                             // Ajax

Route::post('/eventtype/create', 'EventTypeController@store');
Route::delete('/eventtype/{etID}/delete', 'EventTypeController@destroy');
Route::post('/eventtype/{etID}', 'EventTypeController@update');                                            // Ajax

// Member Routes
// ---------------------
Route::get('/members', 'PersonController@index')->name('manageMembers');
Route::get('/merge/{model_code}/{id1?}/{id2?}', 'MergeController@show')->name('showMergeModel');
//Route::get('/find', 'MergeController@find')->name('search');
Route::get('/mbrreport', 'ReportController@member_report')->name('member_report');
Route::get('/autocomplete/{string?}', 'MergeController@query')->name('autocomplete');               // Ajax
Route::post('/merge/{model_code}', 'MergeController@getmodel')->name('step1');
Route::post('/execute_merge', 'MergeController@store')->name('step2');
Route::get('/activity/{id}', 'ActivityController@show')->name('modal_activity');                    // Ajax

Route::get('/search/{query?}', 'PersonController@index2');
Route::post('/search', 'PersonController@search');

// Speaker Routes
// ---------------------
Route::get('/speakers', 'SpeakerController@index')->name('manageSpeakers');
//Route::get('/speakers2', 'SpeakerController@index2')->name('manageSpeakers2');

// Event Routes
// ---------------------
// Event-Registration Routes
Route::post('/regstep1/{event}', 'RegistrationController@processRegForm')->name('register_step1');
Route::get('/regstep2/{event}/{quantity}/{dCode?}', 'RegistrationController@showRegForm');
Route::post('/regstep3/{event}/create', 'RegistrationController@store')->name('register_step2');
Route::get('/confirm_registration/{id}', 'RegFinanceController@show')->name('register_step3');
Route::patch('/complete_registration/{id}', 'RegFinanceController@update')->name('register_step4');
Route::patch('/update_payment/{reg}/{rf}', 'RegFinanceController@update_payment')->name('accept_payment');
Route::post('/reg_verify/{reg}', 'RegistrationController@update');                                         // Ajax
Route::get('/show_receipt/{rf}', 'RegFinanceController@show_receipt');
Route::get('/show_orig/{rf}', 'RegFinanceController@show_receipt_orig');
Route::delete('/cancel_registration/{reg}/{rf}', 'RegistrationController@destroy')->name('cancel_registration');

// Event & Ticket Routes
Route::get('/manage_events/{past?}', 'EventController@index')->name('manageEvents');
Route::post('/activate/{event}', 'EventController@activate');                                              // Ajax
Route::post('/eventajax/{event}', 'EventController@ajax_update');                                          // Ajax
Route::post('/tix/{event}/{ticket?}', 'EventController@get_tix');                                           // Ajax
Route::get('/event/create', 'EventController@create')->name('add_edit_form');
Route::post('/event/create', 'EventController@store')->name('save_event');
Route::get('/event/{event}/edit', 'EventController@edit');
Route::patch('/event/{event}', 'EventController@update')->name('event_update');
Route::delete('/event/{event}', 'EventController@destroy');
Route::get('/eventdiscount/{event}', 'EventDiscountController@show');
Route::post('/eventdiscount', 'EventDiscountController@store');
Route::post('/eventdiscounts/{edID}', 'EventDiscountController@update');
Route::post('/eventdiscountfix/{event}', 'EventDiscountController@fix_defaults');                          // Ajax
Route::delete('/eventdiscount/{id}/delete', 'EventDiscountController@destroy');
Route::post('/eventslug/{id}', 'EventController@checkSlugUniqueness');                                     // Ajax
Route::get('/tracks/{event}', 'TrackController@show');
Route::post('/track/{track}', 'TrackController@update');                                                   // Ajax
Route::post('/eventDays/{event}', 'TrackController@confDaysUpdate');                                       // Ajax
Route::post('/eventsession/{event}', 'TrackController@sessionUpdate');                                     // Ajax
Route::post('/tracksymmetry/{event}', 'TrackController@updateSymmetry');                                   // Ajax
Route::post('/trackticket/{day}', 'TrackController@assignTicketSessions');
Route::delete('/session/{es}', 'EventSessionController@destroy');
Route::patch('/session/{es}', 'EventSessionController@update');
Route::get('/eventreport/{slug}', 'RegistrationController@show');
Route::get('/promote/{reg}', 'RegistrationController@promote');
Route::get('/eventcopy/{slug}', 'EventCopyController@show');
Route::post('/upload/{folder}/{filetype}', 'AssetController@ajax_store');                                  // Ajax

// API Routes that circumvent AUTH and mCentric navigation, etc.
Route::get('/eventlist/{orgID}/{etID}/{override?}', 'EventController@listing');
Route::get('/ticketlist/{eventslug}/{override?}', 'EventController@ticket_listing');
Route::get('/eventics/{orgID}/{etID?}/{override?}', 'EventController@ics_listing');

// Group Registration
Route::get('/group/{event?}/{override?}', 'EventController@showGroup');
Route::post('/getperson', 'MergeController@getperson');                                                    // Ajax
Route::post('/group-reg1', 'RegFinanceController@group_reg1');
Route::get('/groupreg/{rf}', 'RegFinanceController@edit')->name('group_reg1');
Route::patch('/group_reg2/{rf}', 'RegFinanceController@group_reg2');
Route::get('/show_group_receipt/{rf}', 'RegFinanceController@show_group_receipt');

// Data Download Routes
Route::get('/excel/nametags/{event}', 'DownloadController@nametags');
Route::get('/excel/pdudata/{event}', 'DownloadController@pdu_list');
Route::get('/excel/emails/{event}', 'DownloadController@email_list');

// Ticket & Bundle Routes
Route::post('/bundle/{id}', 'BundleController@update');                                                    // Ajax
Route::delete('/bundle/{id}/delete', 'BundleController@destroy')->name('delete_bundle');
Route::post('/ticket/{id}', 'TicketController@update');                                                    // Ajax
Route::post('/tickets/create', 'TicketController@store');
Route::delete('/ticket/{id}/delete', 'TicketController@destroy')->name('delete_ticket');
Route::get('/event-tickets/{id}', 'TicketController@show');
Route::post('/event-tickets/{id}', 'TicketController@show');


// Location Routes
Route::get('/locations', 'LocationController@index');
Route::post('/location/update', 'LocationController@update');                                              // Ajax
Route::get('/locations/{id}', 'LocationController@show');

// Mail Test
Route::get('/mt', 'MailGunController@testmail');
Route::get('/tb', 'MailGunController@bugsnag');

// Campaign Management Routes
// ----------------------------------------------------------------------------------

// Email List Routes
Route::get('/lists', 'EmailListController@index');
Route::post('/list', 'EmailListController@store');
Route::get('/list/{emailList}', 'EmailListController@show');
Route::patch('/list/{list}', 'EmailListController@update')->name('list_update');

// Campaign Routes
Route::get('/c/{campaign}', 'CampaignController@show_campaign');
Route::get('/campaigns', 'CampaignController@index');
Route::get('/campaign/create', 'CampaignController@create');
Route::post('/campaign', 'CampaignController@store');
Route::get('/campaign/{campaign}', 'CampaignController@show');
Route::get('/campaign/{campaign}/edit', 'CampaignController@edit');
Route::patch('/campaign/{campaign}', 'CampaignController@update');

// ----------------------------------------------------------------------------------
Route::get('/testlogin', 'Auth\LoginController@showLoginForm');
//Route::post('/testlogin', 'Auth\LoginController@showLoginForm');
Route::get('/test', function () {
    $events = App\Event::all();
    return view('v1.auth_pages.welcome', compact('events'));
});

Route::get('/twitter/{event}', 'TwitterController@show');

Route::post('approve-tweets', ['middleware' => 'auth', function (Illuminate\Http\Request $request) {
    foreach ($request->all() as $input_key => $input_val) {
        if (strpos($input_key, 'approval-status-') === 0) {
            $tweet_id = substr_replace($input_key, '', 0, strlen('approval-status-'));
            $tweet = App\Tweet::where('id', $tweet_id)->first();
            if ($tweet) {
                $tweet->approved = (int)$input_val;
                $tweet->save();
            }
        }
    }
    return redirect()->back();
}]);

Route::get('/blank', ['middleware' => 'auth', function () {
    return view('v1.auth_pages.page-tmp');
}]);

Auth::routes();


Route::get('ste2', function () {
    Mail::raw('Sending email is easy from ' . env('APP_ENV'), function ($message) {
        $message->subject('Test Email');
        $message->from('support@mCentric.org', 'mCentric Support');
        $message->to('pcartagena@partners.org');
    });
});

Route::get('snaptest', function () {
    // $snap = App::make('snappy.pdf');
    // $snap->generate(env('APP_URL')."/show_orig/159", 'blah.pdf');
    // return $snap->inline();
    return PDF::loadFile(env('APP_URL')."/show_orig/159")->inline('blah.pdf');
});

Route::any('{all}', function () {
    return view('errors.404');
})->where('all', '.*');

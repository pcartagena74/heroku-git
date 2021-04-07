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
/**
 * below code is for language route do not update
 */
Route::get('trigger-dyno', 'DynoController@index');
Route::get('/preview', function () {
    $e = \App\Models\Event::find(319);
    $note = new \App\Notifications\EventICSNote($e);

    return $note->toMail('blah@test.com');
});

Route::get('setlocale/{locale}', function ($locale) {
    if (in_array($locale, \Config::get('app.locales'))) {
        if (Auth::check()) {
            $user = Auth::user();
            $user->locale = $locale;
            $user->update();
        }
    } else {
        $locale = Config::get('app.locale');
    }
    session(['locale' => $locale]);
    Cookie::queue('locale', $locale, 60);

    return redirect()->back();
});

Route::get('/store-address-from-zip', function () {
    storeLatiLongiFormZip();
})->middleware('auth');

// Public Routes
Route::get('/', 'HomeController@index')->name('home');

Route::post('/reportissue', 'ErrorController@reportIssue');

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
Route::post('/discount/{event}', 'EventDiscountController@showDiscount')->name('check_discount'); // Ajax
Route::post('/eLookup/{email}', 'EmailController@show')->name('lookup_email'); // Ajax
Route::post('/oLookup/{pmiid}', 'PublicFunctionController@oLookup')->name('lookup_pmiid'); // Ajax

// Public Session Self Check-in Routes
Route::get('/rs/{session}', 'RegSessionController@show')->name('self_checkin');
Route::post('/rs/{session}/edit', 'RegSessionController@store_session');
Route::get('/rs_survey/{rs}', 'RegSessionController@show_session_survey');
Route::post('/rs_survey', 'RegSessionController@store_survey');
Route::get('/mail_surveys/{event}/{es?}', 'RegSessionController@send_surveys');

// Public Volunteer-Led Check-in Routes
Route::get('/checkin/{event}/{session?}', 'RegSessionController@volunteer_checkin');
Route::post('/process_checkin', 'RegSessionController@process_checkin');
Route::get('/record_attendance/{event}', 'AuthCheckinController@index');
Route::get('/show_record_attendance/{es}', 'AuthCheckinController@show');
Route::post('/record_attendance/{event}', 'AuthCheckinController@store');

Route::get('/storage/events/{filename}', function ($filename) {
    $filePath = Flysystem::connection('awss3')->get($filename);

    return redirect($filePath);
});

// Individual Public Page Routes
// -----------------------------
// Dashboard or Regular User "Home"
Route::get('/dashboard', 'ActivityController@index')->name('dashboard');
Route::post('/networking', 'ActivityController@networking'); // Ajax
// Route::get('/home', 'ActivityController@index');
// as its create confict in left me adding a redirection to dashboard
Route::get('/home', function () {
    return redirect('dashboard');
})->middleware('auth');
Route::get('/upcoming', 'ActivityController@future_index')->name('upcoming_events');
Route::post('/update_sessions/{reg}', 'RegSessionController@update_sessions')->name('update_sessions');
Route::post('/event_checkin/{event}/{session?}', 'RegSessionController@store')->name('default_sess_checkin');

Auth::routes();
Route::get('/ticketit', function () {
    // return redirect(action('\Kordy\Ticketit\Controllers\TicketsController@index'));
});

// Private Admin Page Routes
// -------------------------
Route::get('/newuser/create', 'UserController@create');
Route::post('/newuser', 'UserController@store');
Route::get('/become', 'ActivityController@create');
Route::post('/become', 'ActivityController@become');
Route::get('/panel', 'AdminController@index');
Route::post('/panel', 'AdminController@store');
Route::post('/panel/update', 'AdminController@update');         // VUEJS Route
Route::get('/create_organization', 'OrgController@create');
Route::post('/save_organization', 'OrgController@store');

// My Profile / Member Editing
// ---------------------
// Linked in routes need to be above profile routes so "linkedin" overrides {id}
Route::get('/profile/linkedin', 'PersonController@redirectToLinkedIn');
Route::get('/profile/linkedin/callback', 'PersonController@handleLinkedInCallback');

Route::get('/profile/{id}/{modal?}', 'PersonController@show')->name('showMemberProfile');
Route::post('/profile/{id}', 'PersonController@update');        // Ajax
Route::post('/op/{id}', 'PersonController@update_op');          // Ajax
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
Route::get('/orgsettings', 'OrgController@index');                  //updated for org listing if available
Route::get('/orgsettings/{id}', 'OrgController@show');
Route::post('/orgsettings/{id}', 'OrgController@update');           // Ajax
Route::get('/eventdefaults', 'OrgController@event_defaults');
Route::post('/update-default-org', 'OrgController@updateDefaultOrg');
Route::post('/orgdiscounts/{id}', 'OrgDiscountController@update');  // Ajax

Route::get('/load_data', 'UploadController@index');
Route::post('/load_data', 'UploadController@store');

Route::get('/role_mgmt/{query?}', 'RoleController@index');
Route::post('/role_search', 'RoleController@search');
Route::post('/role/{person}/{role}', 'RoleController@update');      // Ajax

Route::post('/eventtype/create', 'EventTypeController@store');
Route::delete('/eventtype/{etID}/delete', 'EventTypeController@destroy');
Route::post('/eventtype/{etID}', 'EventTypeController@update');    // Ajax

// Member Routes
// ---------------------
Route::get('/members', 'PersonController@index')->name('manageMembers');
Route::get('/merge/{model_code}/{id1?}/{id2?}', 'MergeController@show')->name('showMergeModel');
//Route::get('/find', 'MergeController@find')->name('search');
Route::get('/mbrreport/{id?}', 'ReportController@show')->name('member_report');
Route::post('/mbrreport/{id}', 'ReportController@update');
Route::get('/autocomplete/{string?}', 'MergeController@query')->name('autocomplete'); // Ajax
Route::post('/merge/{model_code}', 'MergeController@getmodel')->name('step1');
Route::post('/execute_merge', 'MergeController@store')->name('step2');
Route::get('/activity/{id}', 'ActivityController@show')->name('modal_activity');      // Ajax
Route::get('/eventstats', 'EventStatsController@index');

Route::get('/search/{query?}', 'PersonController@index2');
Route::post('/search', 'PersonController@search');

// Speaker Routes
// ---------------------
Route::get('/speakers', 'SpeakerController@index')->name('manageSpeakers');
Route::get('/speakers/{speaker}', 'SpeakerController@show');

// Event Routes
// ---------------------
// Event-Registration Routes
Route::post('/regstep1/{event}', 'RegistrationController@processRegForm')->name('register_step1');
Route::get('/regstep2/{event}/{quantity}/{dCode?}', 'RegistrationController@showRegForm');
Route::post('/regstep3/{event}/create', 'RegistrationController@store')->name('register_step2');
Route::get('/confirm_registration/{id}', 'RegFinanceController@show')->name('register_step3');
Route::patch('/complete_registration/{id}', 'RegFinanceController@update')->name('register_step4');
Route::patch('/update_payment/{reg}/{rf}', 'RegFinanceController@update_payment')->name('accept_payment');
Route::post('/reg_verify/{reg}', 'RegistrationController@update'); // Ajax
Route::get('/show_receipt/{rf}', 'RegFinanceController@show_receipt');
Route::get('/recreate_receipt/{rf}', 'RegFinanceController@generate_receipt');
Route::get('/show_orig/{rf}', 'RegFinanceController@show_receipt_orig');
Route::delete('/cancel_registration/{reg}/{rf}', 'RegistrationController@destroy')->name('cancel_registration');

// Event & Ticket Routes
Route::get('/manage_events/{past?}', 'EventController@index')->name('manageEvents');
Route::post('/activate/{event}', 'EventController@activate'); // Ajax
Route::post('/eventajax/{event}', 'EventController@ajax_update'); // Ajax
Route::post('/tix/{event}/{ticket?}', 'EventController@get_tix'); // Ajax
Route::get('/event/create', 'EventController@create')->name('add_edit_event');
Route::post('/event/create', 'EventController@store')->name('save_event');
Route::get('/event/{event}/edit', 'EventController@edit')->name('edit_event');
Route::patch('/event/{event}', 'EventController@update')->name('update_event');
Route::delete('/event/{event}', 'EventController@destroy')->name('delete_event');
Route::get('/eventdiscount/{event}', 'EventDiscountController@show');
Route::post('/eventdiscount', 'EventDiscountController@store');
Route::post('/eventdiscounts/{edID}', 'EventDiscountController@update');
Route::post('/eventdiscountfix/{event}', 'EventDiscountController@fix_defaults'); // Ajax
Route::delete('/eventdiscount/{id}/delete', 'EventDiscountController@destroy');
Route::post('/eventslug/{id}', 'EventController@checkSlugUniqueness'); // Ajax
Route::get('/tracks/{event}', 'TrackController@show');
Route::post('/track/{track}', 'TrackController@update'); // Ajax
Route::post('/eventDays/{event}', 'TrackController@confDaysUpdate'); // Ajax
Route::post('/eventsession/{event}', 'TrackController@sessionUpdate'); // Ajax
Route::post('/tracksymmetry/{event}', 'TrackController@updateSymmetry'); // Ajax
Route::post('/trackticket/{day}', 'TrackController@assignTicketSessions');
Route::patch('/session/{es}', 'EventSessionController@update');
Route::delete('/session/{es}', 'EventSessionController@destroy');
Route::get('/eventreport/{slug}/{format?}', 'RegistrationController@show');
Route::get('/promote/{reg}', 'RegistrationController@promote');
Route::get('/eventcopy/{slug}', 'EventCopyController@show');
Route::post('/upload/{folder}/{filetype}', 'AssetController@ajax_store'); // Ajax

// Public API Routes that circumvent mCentric navigation, etc.
Route::get('/eventlist/{orgID}/{past}/{cal?}/{etID?}/{override?}', 'EventAPIController@show');
Route::get('/ticketlist/{eventslug}/{override?}', 'EventController@ticket_listing');
Route::get('/eventics/{orgID}/{etID?}/{override?}', 'EventController@ics_listing');

// Group Registration
Route::get('/group/{event?}/{override?}', 'EventController@showGroup');
Route::post('/getperson', 'MergeController@getperson'); // Ajax
Route::post('/group-reg1', 'RegFinanceController@group_reg1');
Route::get('/groupreg/{rf}', 'RegFinanceController@edit')->name('group_reg1');
Route::patch('/group_reg2/{rf}', 'RegFinanceController@group_reg2');
Route::get('/show_group_receipt/{rf}', 'RegFinanceController@show_group_receipt');

// Data Download Routes
Route::get('/excel/nametags/{event}', 'DownloadController@nametags');
Route::get('/excel/pdudata/{event}/{es?}', 'DownloadController@pdu_list');
Route::get('/excel/emails/{event}', 'DownloadController@email_list');

// Ticket & Bundle Routes
Route::post('/bundle/{id}', 'BundleController@update'); // Ajax
Route::delete('/bundle/{id}/delete', 'BundleController@destroy')->name('delete_bundle');

Route::post('/ticket/{id}', 'TicketController@update'); // Ajax
Route::post('/tickets/create', 'TicketController@store');

Route::delete('/ticket/{id}/delete', 'TicketController@destroy')->name('delete_ticket');
Route::get('/event-tickets/{id}', 'TicketController@show');
Route::post('/event-tickets/{id}', 'TicketController@show');

// Location Routes
Route::get('/locations', 'LocationController@index');
Route::post('/location/update', 'LocationController@update'); // Ajax
Route::get('/locations/{id}', 'LocationController@show');

// Mail Test
Route::get('/mt', 'MailGunController@testmail');
Route::get('/tb', 'MailGunController@bugsnag');

// Campaign Management Routes
// ----------------------------------------------------------------------------------

// Email List Routes
Route::get('/lists', 'EmailListController@index');
Route::post('/list', 'EmailListController@store')->name('EmailList.Save');
Route::post('/list/update', 'EmailListController@update')->name('EmailList.Update');
Route::post('/list/delete', 'EmailListController@destroy')->name('EmailList.Delete');
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
Route::get('/campaign/{campaign}/copy', 'CampaignController@copy');
Route::post('/deleteCampaign', 'CampaignController@deleteCampaign');
Route::post('/archiveCampaign', 'CampaignController@archiveCampaign');
// Route::patch('/campaign/{campaign}', 'CampaignController@update');
// Email Builder Routes

Route::post('/storeEmailTemplate', 'CampaignController@storeEmailTemplate');
Route::post('/updateEmailTemplate', 'CampaignController@updateEmailTemplate');
Route::get('/getEmailTemplates', 'CampaignController@getEmailTemplates'); //ajax;
Route::post('/getEmailTemplateBlocks', 'CampaignController@getEmailTemplateBlocks'); //ajax;
Route::post('/storeEmailTemplateForPreview', 'CampaignController@storeEmailTemplateForPreview'); //ajax;
Route::get('/preview-email-template/{filename}', 'CampaignController@previewEmailTemplate');
Route::get('/email-template-thumb/{filename}', 'CampaignController@getemailTemplateThumbnailImage');
Route::post('/send-test-email', 'CampaignController@sendTestEmail');
Route::post('/sendCampaign', 'CampaignController@sendCampaign');
Route::post('/campaign/url-clicked-email-list', 'CampaignController@urlClickedEmailList');
Route::post('/email_webhook', 'CampaignController@mailgunWebhook');
Route::get('/list_campaign', 'CampaignController@listCampaign');

// Email Builder Routes ends
// ----------------------------------------------------------------------------------
Route::get('/testlogin', 'Auth\LoginController@showLoginForm');
//Route::post('/testlogin', 'Auth\LoginController@showLoginForm');
Route::get('/mytest', function () {
    $events = App\Models\Event::all();

    return view('v1.auth_pages.welcome', compact('events'));
});

Route::get('/twitter/{event}', 'TwitterController@show');

Route::post('approve-tweets', ['middleware' => 'auth', function (Illuminate\Http\Request $request) {
    foreach ($request->all() as $input_key => $input_val) {
        if (strpos($input_key, 'approval-status-') === 0) {
            $tweet_id = substr_replace($input_key, '', 0, strlen('approval-status-'));
            $tweet = App\Models\Tweet::where('id', $tweet_id)->first();
            if ($tweet) {
                $tweet->approved = (int) $input_val;
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
    Mail::raw('Sending email is easy from '.env('APP_ENV'), function ($message) {
        $message->subject('Test Email');
        $message->from('support@mCentric.org', 'mCentric Support');
        $message->to('pcartagena@partners.org');
    });
});

Route::get('snaptest', function () {
    // $snap = App::make('snappy.pdf');
    // $snap->generate(env('APP_URL')."/show_orig/159", 'blah.pdf');
    // return $snap->inline();
    return PDF::loadFile(env('APP_URL').'/show_orig/159')->inline('blah.pdf');
});

Route::get('library', 'LibraryController@index');
Route::get('getExisitingFile', 'LibraryController@getExisitingFile');
Route::get('/{org_path}/{folder_name}/{file_name}', 'LibraryController@getFile');
Route::post('/get_complete_url', 'LibraryController@getCompleteURL');
// Route::group(['prefix' => 'library-manager', 'middleware' => ['auth']], function () {
//     \UniSharp\LaravelFilemanager\Lfm::routes();
// });
// Route::any('{all}', function () {
//     return view('errors.404');
// })->where('all', '.*');

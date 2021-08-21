<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\Auth;
use App\Http\Controllers\AuthCheckinController;
use App\Http\Controllers\BundleController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\DynoController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\EmailListController;
use App\Http\Controllers\ErrorController;
use App\Http\Controllers\EventAPIController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventCopyController;
use App\Http\Controllers\EventDiscountController;
use App\Http\Controllers\EventSessionController;
use App\Http\Controllers\EventStatsController;
use App\Http\Controllers\EventTypeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MailGunController;
use App\Http\Controllers\MergeController;
use App\Http\Controllers\OrgController;
use App\Http\Controllers\OrgDiscountController;
use App\Http\Controllers\OrgPersonController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\PhoneController;
use App\Http\Controllers\PublicFunctionController;
use App\Http\Controllers\RegFinanceController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\RegSessionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\SpeakerController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\TwitterController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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
Route::get('/linkedin1', [SocialController::class, 'linkedin_login']);

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
Route::get('trigger-dyno', [DynoController::class, 'index']);
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
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::post('/reportissue', [ErrorController::class, 'reportIssue']);

Route::post('/login', [Auth\LoginController::class, 'login'])->name('login');
Route::get('/logout', [Auth\LoginController::class, 'logout'])->name('logout');

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
Route::get('/pmi_lookup/{org}', [OrgPersonController::class, 'index']);
Route::post('/pmi_lookup', [OrgPersonController::class, 'find']);
Route::get('/pmi_account/{person}', [OrgPersonController::class, 'show']);

Route::get('/password/resetmodal', [Auth\ResetPasswordController::class, 'showResetForm_inModal']);
Route::get('/password/forgotmodal', [Auth\ForgotPasswordController::class, 'showLinkRequestForm_inModal']);

// Public Event-related Routes
Route::get('/events/{eventslug}/{override?}', [EventController::class, 'show'])->name('display_event');
Route::post('/discount/{event}', [EventDiscountController::class, 'showDiscount'])->name('check_discount'); // Ajax
Route::post('/eLookup/{email}', [EmailController::class, 'show'])->name('lookup_email'); // Ajax
Route::post('/oLookup/{pmiid}', [PublicFunctionController::class, 'oLookup'])->name('lookup_pmiid'); // Ajax

// Public Session Self Check-in Routes
Route::get('/rs/{session}', [RegSessionController::class, 'show'])->name('self_checkin');
Route::post('/rs/{session}/edit', [RegSessionController::class, 'store_session']);
Route::get('/rs_survey/{rs}', [RegSessionController::class, 'show_session_survey']);
Route::post('/rs_survey', [RegSessionController::class, 'store_survey']);
Route::get('/mail_surveys/{event}/{es?}', [RegSessionController::class, 'send_surveys']);

// Public Volunteer-Led Check-in Routes
Route::get('/checkin/{event}/{session?}', [RegSessionController::class, 'volunteer_checkin']);
Route::post('/process_checkin', [RegSessionController::class, 'process_checkin']);
Route::get('/record_attendance/{event}', [AuthCheckinController::class, 'index']);
Route::get('/show_record_attendance/{es}', [AuthCheckinController::class, 'show']);
Route::post('/record_attendance/{event}', [AuthCheckinController::class, 'store']);

Route::get('/storage/events/{filename}', function ($filename) {
    $filePath = Flysystem::connection('awss3')->get($filename);

    return redirect($filePath);
});

// Individual Public Page Routes
// -----------------------------
// Dashboard or Regular User "Home"
Route::get('/dashboard', [ActivityController::class, 'index'])->name('dashboard');
Route::post('/networking', [ActivityController::class, 'networking']); // Ajax
// Route::get('/home', [ActivityController::class, 'index']);
// as its create confict in left me adding a redirection to dashboard
Route::get('/home', function () {
    return redirect('dashboard');
})->middleware('auth');
Route::get('/upcoming', [ActivityController::class, 'future_index'])->name('upcoming_events');
Route::post('/update_sessions/{reg}', [RegSessionController::class, 'update_sessions'])->name('update_sessions');
Route::post('/event_checkin/{event}/{session?}', [RegSessionController::class, 'store'])->name('default_sess_checkin');

Auth::routes();
Route::get('/ticketit', function () {
    // return redirect(action([\Kordy\Ticketit\Controllers\TicketsController::class, 'index']));
});

// Private Admin Page Routes
// -------------------------
Route::get('/newuser/create', [UserController::class, 'create']);
Route::post('/newuser', [UserController::class, 'store']);
Route::get('/become', [ActivityController::class, 'create']);
Route::post('/become', [ActivityController::class, 'become']);
Route::get('/panel', [AdminController::class, 'index']);
Route::post('/panel', [AdminController::class, 'store']);
Route::post('/panel/update', [AdminController::class, 'update']);         // VUEJS Route
Route::get('/create_organization', [OrgController::class, 'create']);
Route::post('/save_organization', [OrgController::class, 'store']);

// My Profile / Member Editing
// ---------------------
// Linked in routes need to be above profile routes so "linkedin" overrides {id}
Route::get('/profile/linkedin', [PersonController::class, 'redirectToLinkedIn']);
Route::get('/profile/linkedin/callback', [PersonController::class, 'handleLinkedInCallback']);

Route::get('/profile/{id}/{modal?}', [PersonController::class, 'show'])->name('showMemberProfile');
Route::post('/profile/{id}', [PersonController::class, 'update']);        // Ajax
Route::post('/op/{id}', [PersonController::class, 'update_op']);          // Ajax
Route::post('/address/{id}', [AddressController::class, 'update']);
Route::post('/addresses/create', [AddressController::class, 'store']);
Route::post('/locations/create', [LocationController::class, 'store']);
Route::post('/address/{id}/delete', [AddressController::class, 'destroy']);
Route::post('/email/{id}', [EmailController::class, 'update']);
Route::post('/emails/create', [EmailController::class, 'store']);
Route::post('/email/{id}/delete', [EmailController::class, 'destroy']);
Route::post('/phone/{id}', [PhoneController::class, 'update']);
Route::post('/phones/create', [PhoneController::class, 'store']);
Route::post('/phone/{id}/delete', [PhoneController::class, 'destroy']);
Route::post('/password', [PersonController::class, 'change_password']);
Route::get('/force', [PersonController::class, 'show_force']);
Route::post('/force_password', [PersonController::class, 'force_password_change']);

Route::get('/u/{person}/{email}', [PersonController::class, 'undo_login'])->name('UndoLogin');

// Organizational Routes
// ---------------------
// Settings
Route::get('/orgs/my', [OrgController::class, 'index']);                  //updated for org listing if available
Route::get('/orgsettings/{id}', [OrgController::class, 'show']);
Route::post('/orgsettings/{id}', [OrgController::class, 'update']);           // Ajax
Route::get('/eventdefaults', [OrgController::class, 'event_defaults']);
Route::post('/update-default-org', [OrgController::class, 'updateDefaultOrg']);
Route::post('/orgdiscounts/{id}', [OrgDiscountController::class, 'update']);  // Ajax

Route::get('/load_data', [UploadController::class, 'index']);
Route::post('/load_data', [UploadController::class, 'store']);

Route::get('/role_mgmt/{query?}', [RoleController::class, 'index']);
Route::post('/role_search', [RoleController::class, 'search']);
Route::post('/role/{person}/{role}', [RoleController::class, 'update']);      // Ajax

Route::post('/eventtype/create', [EventTypeController::class, 'store']);
Route::delete('/eventtype/{etID}/delete', [EventTypeController::class, 'destroy']);
Route::post('/eventtype/{etID}', [EventTypeController::class, 'update']);    // Ajax

// Member Routes
// ---------------------
Route::get('/members', [PersonController::class, 'index'])->name('manageMembers');
Route::get('/merge/{model_code}/{id1?}/{id2?}', [MergeController::class, 'show'])->name('showMergeModel');
//Route::get('/find', [MergeController::class, 'find'])->name('search');
Route::get('/mbrreport/{id?}', [ReportController::class, 'show'])->name('member_report');
Route::post('/mbrreport/{id}', [ReportController::class, 'update']);
Route::get('/autocomplete/{string?}', [MergeController::class, 'query'])->name('autocomplete'); // Ajax
Route::post('/merge/{model_code}', [MergeController::class, 'getmodel'])->name('step1');
Route::post('/execute_merge', [MergeController::class, 'store'])->name('step2');
Route::get('/activity/{id}', [ActivityController::class, 'show'])->name('modal_activity');      // Ajax
Route::get('/eventstats', [EventStatsController::class, 'index']);

Route::get('/search/{query?}', [PersonController::class, 'index2']);
Route::post('/search', [PersonController::class, 'search']);

// Speaker Routes
// ---------------------
Route::get('/speakers', [SpeakerController::class, 'index'])->name('manageSpeakers');
Route::get('/speakers/{speaker}', [SpeakerController::class, 'show']);
Route::get('/s2', [SpeakerController::class, 'index2'])->name('manageSpeakers2');

// Event Routes
// ---------------------
// Event-Registration Routes
Route::post('/regstep1/{event}', [RegistrationController::class, 'processRegForm'])->name('register_step1');
Route::get('/regstep2/{event}/{quantity}/{dCode?}', [RegistrationController::class, 'showRegForm']);
Route::post('/regstep3/{event}/create', [RegistrationController::class, 'store'])->name('register_step2');
Route::get('/confirm_registration/{id}', [RegFinanceController::class, 'show'])->name('register_step3');
Route::patch('/complete_registration/{id}', [RegFinanceController::class, 'update'])->name('register_step4');
Route::patch('/update_payment/{reg}/{rf}', [RegFinanceController::class, 'update_payment'])->name('accept_payment');
Route::post('/reg_verify/{reg}', [RegistrationController::class, 'update']); // Ajax
Route::get('/show_receipt/{rf}', [RegFinanceController::class, 'show_receipt']);
Route::get('/recreate_receipt/{rf}', [RegFinanceController::class, 'generate_receipt']);
Route::get('/show_orig/{rf}', [RegFinanceController::class, 'show_receipt_orig']);
Route::delete('/cancel_registration/{reg}/{rf}', [RegistrationController::class, 'destroy'])->name('cancel_registration');

// Event & Ticket Routes
Route::get('/manage_events/{past?}', [EventController::class, 'index'])->name('manageEvents');
Route::post('/activate/{event}', [EventController::class, 'activate']); // Ajax
Route::post('/eventajax/{event}', [EventController::class, 'ajax_update']); // Ajax
Route::post('/tix/{event}/{ticket?}', [EventController::class, 'get_tix']); // Ajax
Route::get('/event/create', [EventController::class, 'create'])->name('add_edit_event');
Route::post('/event/create', [EventController::class, 'store'])->name('save_event');
Route::get('/event/{event}/edit', [EventController::class, 'edit'])->name('edit_event');
Route::patch('/event/{event}', [EventController::class, 'update'])->name('update_event');
Route::delete('/event/{event}', [EventController::class, 'destroy'])->name('delete_event');
Route::get('/eventdiscount/{event}', [EventDiscountController::class, 'show']);
Route::post('/eventdiscount', [EventDiscountController::class, 'store']);
Route::post('/eventdiscounts/{edID}', [EventDiscountController::class, 'update']);
Route::post('/eventdiscountfix/{event}', [EventDiscountController::class, 'fix_defaults']); // Ajax
Route::delete('/eventdiscount/{id}/delete', [EventDiscountController::class, 'destroy']);
Route::post('/eventslug/{id}', [EventController::class, 'checkSlugUniqueness']); // Ajax
Route::get('/tracks/{event}', [TrackController::class, 'show']);
Route::post('/track/{track}', [TrackController::class, 'update']); // Ajax
Route::post('/eventDays/{event}', [TrackController::class, 'confDaysUpdate']); // Ajax
Route::post('/eventsession/{event}', [TrackController::class, 'sessionUpdate']); // Ajax
Route::post('/tracksymmetry/{event}', [TrackController::class, 'updateSymmetry']); // Ajax
Route::post('/trackticket/{day}', [TrackController::class, 'assignTicketSessions']);
Route::patch('/session/{es}', [EventSessionController::class, 'update']);
Route::delete('/session/{es}', [EventSessionController::class, 'destroy']);
Route::get('/eventreport/{slug}/{format?}', [RegistrationController::class, 'show']);
Route::get('/promote/{reg}', [RegistrationController::class, 'promote']);
Route::get('/eventcopy/{slug}', [EventCopyController::class, 'show']);
Route::post('/upload/{folder}/{filetype}', [AssetController::class, 'ajax_store']); // Ajax

// Public API Routes that circumvent mCentric navigation, etc.
Route::get('/eventlist/{orgID}/{past}/{cal?}/{etID?}/{override?}', [EventAPIController::class, 'show']);
Route::get('/ticketlist/{eventslug}/{override?}', [EventController::class, 'ticket_listing']);
Route::get('/eventics/{orgID}/{etID?}/{override?}', [EventController::class, 'ics_listing']);

// Group Registration
Route::get('/group/{event?}/{override?}', [EventController::class, 'showGroup']);
Route::post('/getperson', [MergeController::class, 'getperson']); // Ajax
Route::post('/group-reg1', [RegFinanceController::class, 'group_reg1']);
Route::get('/groupreg/{rf}', [RegFinanceController::class, 'edit'])->name('group_reg1');
Route::patch('/group_reg2/{rf}', [RegFinanceController::class, 'group_reg2']);
Route::get('/show_group_receipt/{rf}', [RegFinanceController::class, 'show_group_receipt']);

// Data Download Routes
Route::get('/excel/nametags/{event}', [DownloadController::class, 'nametags']);
Route::get('/excel/pdudata/{event}/{es?}', [DownloadController::class, 'pdu_list']);
Route::get('/excel/emails/{event}', [DownloadController::class, 'email_list']);

// Ticket & Bundle Routes
Route::post('/bundle/{id}', [BundleController::class, 'update']); // Ajax
Route::delete('/bundle/{id}/delete', [BundleController::class, 'destroy'])->name('delete_bundle');

Route::post('/ticket/{id}', [TicketController::class, 'update']); // Ajax
Route::post('/tickets/create', [TicketController::class, 'store']);

Route::delete('/ticket/{id}/delete', [TicketController::class, 'destroy'])->name('delete_ticket');
Route::get('/event-tickets/{id}', [TicketController::class, 'show']);
Route::post('/event-tickets/{id}', [TicketController::class, 'show']);

// Location Routes
Route::get('/locations', [LocationController::class, 'index']);
Route::post('/location/update', [LocationController::class, 'update']); // Ajax
Route::get('/locations/{id}', [LocationController::class, 'show']);

// Mail Test
Route::get('/mt', [MailGunController::class, 'testmail']);
Route::get('/tb', [MailGunController::class, 'bugsnag']);

// Campaign Management Routes
// ----------------------------------------------------------------------------------

// Email List Routes
Route::get('/lists', [EmailListController::class, 'index']);
Route::post('/list', [EmailListController::class, 'store'])->name('EmailList.Save');
Route::post('/list/update', [EmailListController::class, 'update'])->name('EmailList.Update');
Route::post('/list/delete', [EmailListController::class, 'destroy'])->name('EmailList.Delete');
Route::get('/list/{emailList}', [EmailListController::class, 'show']);
Route::patch('/list/{list}', [EmailListController::class, 'update'])->name('list_update');

// Campaign Routes
Route::get('/c/{campaign}', [CampaignController::class, 'show_campaign']);
Route::get('/campaigns', [CampaignController::class, 'index']);
Route::get('/campaign/create', [CampaignController::class, 'create']);
Route::post('/campaign', [CampaignController::class, 'store']);
Route::get('/campaign/{campaign}', [CampaignController::class, 'show']);
Route::get('/campaign/{campaign}/edit', [CampaignController::class, 'edit']);
Route::patch('/campaign/{campaign}', [CampaignController::class, 'update']);
Route::get('/campaign/{campaign}/copy', [CampaignController::class, 'copy']);
Route::post('/deleteCampaign', [CampaignController::class, 'deleteCampaign']);
Route::post('/archiveCampaign', [CampaignController::class, 'archiveCampaign']);
// Route::patch('/campaign/{campaign}', [CampaignController::class, 'update']);
// Email Builder Routes

Route::post('/storeEmailTemplate', [CampaignController::class, 'storeEmailTemplate']);
Route::post('/updateEmailTemplate', [CampaignController::class, 'updateEmailTemplate']);
Route::get('/getEmailTemplates', [CampaignController::class, 'getEmailTemplates']); //ajax;
Route::post('/getEmailTemplateBlocks', [CampaignController::class, 'getEmailTemplateBlocks']); //ajax;
Route::post('/storeEmailTemplateForPreview', [CampaignController::class, 'storeEmailTemplateForPreview']); //ajax;
Route::get('/preview-email-template/{filename}', [CampaignController::class, 'previewEmailTemplate']);
Route::get('/email-template-thumb/{filename}', [CampaignController::class, 'getemailTemplateThumbnailImage']);
Route::post('/send-test-email', [CampaignController::class, 'sendTestEmail']);
Route::post('/sendCampaign', [CampaignController::class, 'sendCampaign']);
Route::post('/campaign/url-clicked-email-list', [CampaignController::class, 'urlClickedEmailList']);
Route::post('/email_webhook', [CampaignController::class, 'mailgunWebhook']);
Route::get('/list_campaign', [CampaignController::class, 'listCampaign']);

// Email Builder Routes ends
// ----------------------------------------------------------------------------------
Route::get('/testlogin', [Auth\LoginController::class, 'showLoginForm']);
//Route::post('/testlogin', [Auth\LoginController::class, 'showLoginForm']);
Route::get('/mytest', function () {
    $events = App\Models\Event::all();

    return view('v1.auth_pages.welcome', compact('events'));
});

Route::get('/twitter/{event}', [TwitterController::class, 'show']);

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

Route::get('library', [LibraryController::class, 'index']);
Route::get('getExisitingFile', [LibraryController::class, 'getExisitingFile']);
Route::get('/{org_path}/{folder_name}/{file_name}', [LibraryController::class, 'getFile']);
Route::post('/get_complete_url', [LibraryController::class, 'getCompleteURL']);
// Route::group(['prefix' => 'library-manager', 'middleware' => ['auth']], function () {
//     \UniSharp\LaravelFilemanager\Lfm::routes();
// });
// Route::any('{all}', function () {
//     return view('errors.404');
// })->where('all', '.*');

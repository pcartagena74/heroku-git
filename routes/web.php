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

// Public Routes
Route::get('/', 'SessionController@create')->name('main_page');
Route::get('/login', 'SessionController@create');
Route::post('/login', 'SessionController@store');

// Public Event-related Routes
Route::get('/events/{event}', 'EventController@show')->name('display_event');
Route::post('/discount/{event}', 'EventDiscountController@showDiscount')->name('check_discount');
Route::get('/register/{ticket}', 'RegistrationController@showRegForm')->name('register_step1');
Route::post('/register/{event}/create', 'RegistrationController@store')->name('register_step2');
Route::get('/confirm_registration/{id}', 'RegFinanceController@show')->name('register_step3');
Route::post('/complete_registration/{id}', 'RegFinanceController@update');
Route::post('/reg_verify/{reg}', 'RegistrationController@update');

Route::get('/password/resetmodal', 'Auth\ResetPasswordController@showResetForm_inModal');
Route::get('/password/forgotmodal', 'Auth\ForgotPasswordController@showLinkRequestForm_inModal');

Route::get('/policies', function(){
    return view('v1.public_pages.policies');
});

// Individual Page Routes

// Dashboard
Route::get('/dashboard', 'ActivityController@index')->name('dashboard');
Route::get('/home', 'ActivityController@index')->name('home');

// My Profile / Member Editing
Route::get('/profile/{id}', 'PersonController@show')->name('showMemberProfile');
Route::post('/profile/{id}', 'PersonController@update');
Route::post('/address/{id}', 'AddressController@update');
Route::post('/addresses/create', 'AddressController@store');
Route::post('/address/{id}/delete', 'AddressController@destroy');
Route::post('/email/{id}', 'EmailController@update');
Route::post('/emails/create', 'EmailController@store');
Route::post('/email/{id}/delete', 'EmailController@destroy');


// Event & Ticket Routes
Route::get('/events', 'EventController@index')->name('manageEvents');
Route::post('/activate/{event}', 'EventController@activate');
Route::post('/eventajax/{event}', 'EventController@ajax_update');
Route::get('/event/create', 'EventController@create')->name('add_edit_form');
Route::post('/event/create', 'EventController@store')->name('save_event');
Route::get('/event/{event}/edit', 'EventController@edit');
Route::patch('/event/{event}', 'EventController@update')->name('event_update');
Route::delete('/event/{event}', 'EventController@destroy');
Route::get('/eventdiscount/{event}', 'EventDiscountController@show');
Route::post('/eventdiscount', 'EventDiscountController@store');
Route::delete('/eventdiscount/{id}/delete', 'EventDiscountController@destroy');

// Ticket & Bundle Routes
Route::post('/bundle/{id}', 'BundleController@update');
Route::delete('/bundle/{id}/delete', 'BundleController@destroy')->name('delete_bundle');
Route::post('/ticket/{id}', 'TicketController@update');
Route::post('/tickets/create', 'TicketController@store');
Route::delete('/ticket/{id}/delete', 'TicketController@destroy')->name('delete_ticket');
Route::get('/event-tickets/{id}', 'TicketController@show');
Route::post('/event-tickets/{id}', 'TicketController@show');


// Location Routes
Route::get('/locations', 'LocationController@index');
Route::post('/location/update', 'LocationController@update');
//Route::get('/location/update', 'LocationController@update');
//Route::post('/locations/{id}', 'LocationController@show');
Route::get('/locations/{id}', 'LocationController@show');

// Route::patch('/events/{event}', 'EventController@edit');

//Route::get('/home', 'HomeController@index');

// Organizational Routes
// ---------------------
// Settings
Route::get('/orgsettings', 'OrgController@index');
Route::get('/orgsettings/{id}', 'OrgController@show');
Route::post('/orgsettings/{id}', 'OrgController@update');
Route::get('/eventdefaults', 'OrgController@event_defaults');
Route::post('/orgdiscounts/{id}', 'OrgDiscountController@update');

// Member Routes
Route::get('/members', 'PersonController@index')->name('manageMembers');


// ----------------------------------------------------------------------------------
Route::get('/testlogin', 'Auth\LoginController@showLoginForm');
//Route::post('/testlogin', 'Auth\LoginController@showLoginForm');
Route::get('/test', function() {
    $events = App\Event::all();
    return view('v1.auth_pages.welcome', compact('events'));
});

Route::get('/twitter/{id}', 'TwitterController@show');

Route::post('approve-tweets', ['middleware' => 'auth', function (Illuminate\Http\Request $request) {
    foreach ($request->all() as $input_key => $input_val) {
        if ( strpos($input_key, 'approval-status-') === 0 ) {
            $tweet_id = substr_replace($input_key, '', 0, strlen('approval-status-'));
            $tweet = App\Tweet::where('id',$tweet_id)->first();
            if ($tweet) {
                $tweet->approved = (int)$input_val;
                $tweet->save();
            }
        }
    }
    return redirect()->back();
}]);


Route::get('/logout', 'SessionController@logout');

Auth::routes();

<?php

namespace App\Http\Controllers;

use App\OrgPerson;
use App\PersonSocialite;
use App\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Address;
use App\Email;
use App\Person;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Cache;
use App\Notifications\PasswordChange;
use App\Notifications\LoginChange;
use App\Notifications\UndoLoginChange;

class PersonController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['oLookup']]);

        $this->middleware(function (Request $request, $next) {
            if(auth()){
                $this->currentPerson = Person::find(auth()->user()->id);
            }
            return $next($request);
        });
    }


    // Helper function containing code to put the member counting bits into a blade template
    protected function member_bits() {
        $topBits             = [];
        $total_people        = Cache::get('total_people', function () {
            return DB::table('person')
                ->join('org-person', 'org-person.personID', '=', 'person.personID')
                ->where([
                    ['person.personID', '!=', 1],
                    ['org-person.orgID', '=', $this->currentPerson->defaultOrgID]
                ])->count();
        });
        $individual          = trans('messages.headers.p_ind');
        $individuals         = Cache::get('individual_data', function () use($individual) {

            return DB::table('person')
                ->join('org-person', 'org-person.personID', '=', 'person.personID')
                ->where([
                    ['person.personID', '!=', 1],
                    ['OrgStat2', '=', $individual],
                    ['org-person.orgID', '=', $this->currentPerson->defaultOrgID]
                ])->count();
        });
        $student             = trans('messages.headers.p_stu');
        $students            = Cache::get('student_data', function () use($student) {

            return DB::table('person')
                ->join('org-person', 'org-person.personID', '=', 'person.personID')
                ->where([
                    ['person.personID', '!=', 1],
                    ['OrgStat2', '=', $student],
                    ['org-person.orgID', '=', $this->currentPerson->defaultOrgID]
                ])->count();
        });
        $retiree             = trans('messages.headers.p_ret');
        $retirees            = Cache::get('retiree_data', function () use($retiree) {

            return DB::table('person')
                ->join('org-person', 'org-person.personID', '=', 'person.personID')
                ->where([
                    ['person.personID', '!=', 1],
                    ['OrgStat2', '=', $retiree],
                    ['org-person.orgID', '=', $this->currentPerson->defaultOrgID]
                ])->count();
        });

        array_push($topBits, [9, trans('messages.headers.tot_peeps'), $total_people, '', '', '', 2]);
        $inds = implode(' ', array($individual, trans_choice('messages.headers.member', 2)));
        $rets = implode(' ', array($retiree, trans_choice('messages.headers.member', 2)));
        $stud = implode(' ', array($student, trans_choice('messages.headers.member', 2)));
        array_push($topBits, [1, $inds, $individuals, '', '', '', 2]);
        array_push($topBits, [1, $rets, $retirees, '', '', '', 2]);
        array_push($topBits, [1, $stud, $students, '', '', '', 2]);

        return($topBits);
    }

    // Shows member management page
    public function index()
    {
        // responds to GET /members; This is for member management page
        //$this->currentPerson = Person::find(auth()->user()->id);

        $topBits = $this->member_bits();

        $mbr_list = Cache::get('mbr_list', function () {
            return OrgPerson::join('person as p', 'p.personID', '=', 'org-person.personID')
                            ->where([
                                ['org-person.orgID', '=', $this->currentPerson->defaultOrgID],
                                ['p.personID', '!=', 1]
                            ])->select(DB::raw("p.personID, concat(firstName, ' ', lastName) AS fullName, OrgStat1, OrgStat2, compName, 
                           title, indName, date_format(RelDate4, '%l/%d/%Y') AS 'Expire', 
                           (SELECT count(*) AS 'cnt' FROM `event-registration` er
						 WHERE er.personID=p.personID) AS 'cnt'"))
                            ->get();
        });

        return view('v1.auth_pages.members.list', compact('topBits', 'mbr_list'));
    }

   /*
    *  index2: placeholder for member search function
    */

    public function index2($query = null) {

        $topBits = $this->member_bits();
        $mbr_list = null;

        if($query !== null){
            $mbr_list = Person::where('firstName', 'LIKE', "%$query%")
                ->orWhere('person.personID', 'LIKE', "%$query%")
                ->orWhere('lastName', 'LIKE', "%$query%")
                ->orWhere('login', 'LIKE', "%$query%")
                ->orWhereHas('orgperson', function ($q) use ($query) {
                    $q->where('OrgStat1', 'LIKE', "%$query%");
                })
                ->orWhereHas('emails', function ($q) use ($query) {
                    $q->where('emailADDR', 'LIKE', "%$query%");
                })
                ->whereHas('orgs', function ($q) {
                    $q->where('organization.orgID', '=', $this->currentPerson->defaultOrgID);
                })
                ->join('org-person as op', 'op.personID', '=', 'person.personID')
                ->select(DB::raw("person.personID, concat(coalesce(`firstName`, ''), ' ', coalesce(`lastName`, '')) AS fullName, op.OrgStat1, op.OrgStat2, compName, 
                           title, indName, date_format(RelDate4, '%l/%d/%Y') AS 'Expire', 
                           (SELECT count(*) AS 'cnt' FROM `event-registration` er WHERE er.personID=person.personID) AS 'cnt'"))
                ->get();

            return view('v1.auth_pages.members.member_search', compact('topBits', 'mbr_list'));
        }

        return view('v1.auth_pages.members.member_search', compact('topBits', 'mbr_list'));
    }

    /*
     *  search: display for member search function
     */

    public function search(Request $request) {
        $string = $request->input('string');
        return redirect(env('APP_URL').'/search/'.$string);
    }

    // Shows profile information for chosen person (or self)
    public function show($id, $modal = null)
    {
        // responds to GET /profile/{id}
        $this->currentPerson = Person::where('personID', '=', auth()->user()->id)->with('socialites')->first();
        if ($id == 'my') {
            // set $id to the logged in Person, otherwise keep the $id given
            $id = $this->currentPerson->personID;
            if (request()->query() != null) {
                if ($this->currentPerson->avatarURL === null
                    && !$this->currentPerson->socialites->contains('providerName', 'LinkedIN')
                ) {
                    $user              = Socialite::driver('linkedin')->user();
                    $person            = Person::find($id);
                    $person->avatarURL = $user->avatar;
                    $person->updaterID = $id;
                    $person->save();

                    $socialite               = new PersonSocialite;
                    $socialite->personID     = $person->personID;
                    $socialite->providerID   = $user->id;
                    $socialite->providerName = 'LinkedIN';
                    $socialite->token        = $user->token;
                    $socialite->save();
                }
            }
        }

        try {
            $profile = Person::where('person.personID', $id)
                ->join('users as u', 'u.id', '=', 'person.personID')
                ->join('org-person as op', function ($join) {
                    $join->on('op.personID', '=', 'person.personID');
                    $join->on('op.orgID', '=', 'person.defaultOrgID');
                })
                ->join('organization as o', 'o.orgID', '=', 'person.defaultOrgID')
                ->select(DB::raw("person.prefix, person.firstName, person.midName, person.lastName, person.suffix,
                                        person.prefName, u.login, person.title, person.compName, person.indName,
                                        person.experience, person.chapterRole, person.defaultOrgID, person.affiliation,
                                        person.allergenInfo, person.allergenNote, person.twitterHandle, person.certifications,
                    OrgStat1, OrgStat2, OrgStat3, OrgStat4, OrgStat5, OrgStat6, OrgStat7, OrgStat8, OrgStat9, OrgStat10,
                    RelDate1, RelDate2, RelDate3, RelDate4, RelDate5, RelDate6, RelDate7, RelDate8, RelDate9, RelDate10,
                    OSN1, OSN2, OSN3, OSN4, OSN5, OSN6, OSN7, OSN8, OSN9, OSN10, 
                    ODN1, ODN2, ODN3, ODN4, ODN5, ODN6, RelDate7, ODN8, ODN9, ODN10, person.personID"))->first();
        } catch (\Exception $exception) {
            request()->session()->flash('alert-danger', trans('messages.errors.no_id', ['id' => $id, 'errormsg' => $exception->getMessage()]));
            return redirect(env('APP_URL').'/profile/my');

        }

        if($profile === null){
            request()->session()->flash('alert-danger', trans('messages.errors.no_id', ['id' => $id,
                                        'modifier' => trans('messages.fields.member'), 'errormsg' => null]));
            return redirect(env('APP_URL').'/profile/my');
        }

        if($profile != $this->currentPerson){
            $u = User::find($profile->personID);
            if($u->password === null){
                request()->session()->flash('alert-warning', trans('messages.instructions.no_user_pass'));
            }
        }

        $topBits = '';

        $prefixes = DB::table('prefixes')->get();
        $prefix_array = ['' => trans('messages.fields.prefixes.select')] +
            $prefixes->pluck('prefix', 'prefix')->map(function($item, $key) {
                return trans('messages.fields.prefixes.'.$item);
            })->toArray();
        $prefixes = $prefix_array;

        $industries = DB::table('industries')->orderBy('industryName')->get();
        $industry_array = ['' => trans('messages.fields.industries.select')] +
            $industries->pluck('industryName', 'industryName')->map(function($item, $key) {
                return trans('messages.fields.industries.'.$item);
            })->toArray();
        $industries = $industry_array;

        $addrTypes  = DB::table('address-type')->get();
        $emailTypes = DB::table('email-type')->get();
        $phoneTypes = DB::table('phone-type')->get();

        $certs = DB::table('certifications')->get();
        $cert_array = $certs->toArray();

        $addresses =
            Address::where('personID', $id)->select('addrID', 'addrTYPE', 'addr1', 'addr2', 'city', 'state', 'zip', 'cntryID')->get();
        $countries = DB::table('countries')->select('cntryID', 'cntryName')->get();

        $emails =
            Email::where('personID', $id)->select('emailID', 'emailTYPE', 'emailADDR', 'isPrimary')->orderBy('isPrimary', 'DESC')->get();

        $phones =
            Phone::where('personID', $id)->select('phoneID', 'phoneType', 'phoneNumber')->get();
        
        return view(
            'v1.auth_pages.members.profile',
            compact('profile', 'topBits', 'prefixes', 'industries', 'addresses', 'emails', 'addrTypes', 'emailTypes', 'countries', 'phones', 'phoneTypes', 'cert_array')
        );
    }

    public function create()
    {
        // responds to /blah/create and shows add/edit form
    }

    public function store(Request $request)
    {
        // responds to POST to /blah and creates, adds, stores the event
        dd(request()->all());
    }

    public function update(Request $request, $id)
    {
        // responds to POST /profile/{id} and is an AJAX call
        $personID = request()->input('pk');

        $name = request()->input('name');
        if (strpos($name, '-')) {
            // if passed from the registration receipt, the $name will have a dash
            list($name, $field) = array_pad(explode("-", $name, 2), 2, null);
        }
        $value = request()->input('value');
        $person = Person::find($personID);
        $updater = auth()->user()->id;

        if ($name == 'login') {
            // when changing login we need to:
            // 1. update user->login, user->email, and person->login with the new values

            $user = User::find($id);
            $orig_email = $user->login;
            $user->login = $value;
            $user->name = $value;
            $user->email = $value;
            $user->save();

            // 2. trigger a notification to be sent to the old email address
            $person->notify(new LoginChange($person, $orig_email));

            $person->login = $value;
            $person->updaterID = $updater;
            $person->save();

            $orig = Email::where('emailADDR', '=', $orig_email)->first();
            $orig->isPrimary = 0;
            $orig->updaterID = $updater;
            $orig->save();

            // 3. change the Email::isPrimary field on the old and new primary email (the one where email will be sent)
            $new_email = $value;
            $new = Email::where('emailADDR', '=', $new_email)->first();
            $new->isPrimary = 1;
            $new->updaterID = $updater;
            $new->save();

        } elseif ($name == 'affiliation') {
            $value = implode(",", (array)$value);
            $person->affiliation = $value;
            $person->updaterID = $updater;
            $person->save();

        } elseif ($name == 'allergenInfo') {
            $value = implode(",", (array)$value);
            $person->allergenInfo = $value;
            $person->updaterID = $updater;
            $person->save();

        } elseif ($name == 'certifications') {
            $value = implode(",", (array)$value);
            $person->certifications = $value;
            $person->updaterID = $updater;
            $person->save();

        } elseif ($name == 'prefix') {
            if(strlen($value) > 10) {
                $value = substr($value, 0, 10);
            }
            $person->prefix = $value;
            $person->updaterID = $updater;
            $person->save();
        } else {
            $person->{$name}   = $value;
            $person->updaterID = $updater;
            $person->save();
        }
        return json_encode(array('status' => 'success', 'name' => $name, 'value' => $value, 'pk' => $personID));
    }

    public function update_op(Request $request, $id){
        // responds to POST /op/{id} and is an AJAX call
        $personID = request()->input('pk');
        $updater = auth()->user()->id;

        $name = request()->input('name');
        if (strpos($name, '-')) {
            // if passed from the registration receipt, the $name will have a dash
            list($name, $field) = array_pad(explode("-", $name, 2), 2, null);
        }
        $value = request()->input('value');
        $person = Person::find($personID);
        $op = OrgPerson::where([
            ['personID', '=', $person->personID],
            ['orgID', '=', $person->defaultOrgID]
        ])->first();

        $op->updaterID = $updater;
        $op->$name = $value;
        $op->save();
        return json_encode(array('status' => 'success', 'name' => $name, 'value' => $value, 'pk' => $personID));
    }

    public function undo_login(Person $person, $string)
    {
        $email = decrypt($string);
        $user = User::find($person->personID);
        $user->login = $email;
        $user->email = $email;
        $user->name  = $email;
        $user->save();

        $e = Email::where('emailADDR', $person->login)->first();
        $e->isPrimary = 0;
        $e->save();

        $e = Email::where('emailADDR', $email)->first();
        $e->isPrimary = 1;
        $e->save();

        $person->login = $email;
        $person->save();

        $person->notify(new UndoLoginChange($person));

        $header = trans('messages.headers.success');
        $message = trans('messages.messages.undo_login', ['email' => $email]);
        return view('v1.public_pages.thanks', compact('header', 'message'));
    }

    public function change_password(Request $request)
    {
        $curPass               = request()->input('curPass');
        $password              = request()->input('password');

        // validate password matching
        $validator = Validator::make($request->all(), [
            'curPass' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput(['tab' => 'tab_content2']);
        }

        $user = User::find(auth()->id());
        $person = Person::find($user->id);

        // validate $curPass
        if (Hash::check($curPass, $user->password)) {
            // update password
            $user->password = bcrypt($password);
            $user->save();
            request()->session()->flash('alert-success', trans('messages.messages.pass_change'));

            // send notification
            // $person->notify(new PasswordChange($person));
            // request()->session()->flash('alert-info', "A confirmation email was sent to $person->login.");

            return back()->withInput(['tab' => 'tab_content2']);
        } else {
            request()->session()->flash('alert-danger', trans('messages.messages.no_curr_pass_match'));
            return back()
                ->withErrors($validator)
                ->withInput(['tab' => 'tab_content2']);
        }
    }

    /**
     * Shows the form to force-change a user's password; meant for CAMI, etc.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show_force(){
        $topBits = '';
        return view('v1.auth_pages.members.force_pass_change', compact('topBits'));
    }
    /**
     * This is just like the above but it doesn't ask for the prior password
     *
     * @return Response
     */
    public function force_password_change(Request $request)
    {
        $password  = request()->input('password');
        $userid    = request()->input('userid');

        // validate password matching
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator);
                //->withInput(['tab' => 'tab_content2']);
        }

        $user = User::find($userid);
        $person = Person::find($user->id);

        // update password
        $user->password = bcrypt($password);
        $user->save();
        request()->session()->flash('alert-success', trans('messages.messages.pass_change_for', ['name' => $person->showFullName()]));

        // send notification
        $person->notify(new PasswordChange($person));
        request()->session()->flash('alert-info', trans('messages.messages.confirm_msg', ['name' => $person->login]));

        return back(); //->withInput(['tab' => 'tab_content2']);
    }

    /**
     * Redirect the user to the LinkedIn authentication page.
     *
     * @return Response
     */
    public function redirectToLinkedIn()
    {
        return Socialite::driver('linkedin')->redirect();
    }

    /**
     * Obtain the user information from LinkedIn
     *
     * @return Response
     */
    public function handleLinkedInCallback()
    {
        $user = Socialite::driver('linkedin')->user();
        //dd($user);
    }

    public function oLookup($pmi_id){
        $op = OrgPerson::where('OrgStat1', '=', $pmi_id)->first();

        if(null !== $op){
            $u = User::where('id', '=', $op->personID)->first();
            if(null !== $u){
                $x = $u->password ? 1: 0;
            } else {
                $x = 1;
            }
            $p = Person::with('orgperson')->where('personID', '=', $op->personID)->first();
            return json_encode(array('status' => 'success', 'p' => $p, 'pass' => $x,
                               'msg' => trans('messages.modals.confirm2', ['fullname' => $p->showFullName()])));
        } else {
            return json_encode(array('status' => 'error', 'p' => null, 'op' => $op, 'pmi_id' => $pmi_id));
        }
    }
}

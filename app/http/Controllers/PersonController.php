<?php

namespace App\Http\Controllers;

use App\OrgPerson;
use App\PersonSocialite;
use App\Phone;
use Illuminate\Http\Request;
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
    public function __construct () {
        $this->middleware('auth');
    }

    // Shows member management page
    public function index () {
        // responds to GET /members; This is for member management page
        $this->currentPerson = Person::find(auth()->user()->id);
        $topBits             = [];
        $total_people        = Cache::get('total_people', function() {
            return DB::table('person')
                     ->join('org-person', 'org-person.personID', '=', 'person.personID')
                     ->where([
                         ['person.personID', '!=', 1],
                         ['org-person.orgID', '=', $this->currentPerson->defaultOrgID]
                     ])->count();
        });
        $individual          = 'Individual';
        $individuals         = Cache::get('individual_data', function() {
            $individual = 'Individual';
            return DB::table('person')
                     ->join('org-person', 'org-person.personID', '=', 'person.personID')
                     ->where([
                         ['person.personID', '!=', 1],
                         ['OrgStat2', '=', $individual],
                         ['org-person.orgID', '=', $this->currentPerson->defaultOrgID]
                     ])->count();
        });
        $student             = 'Student';
        $students            = Cache::get('student_data', function() {
            $student = 'Student';
            return DB::table('person')
                     ->join('org-person', 'org-person.personID', '=', 'person.personID')
                     ->where([
                         ['person.personID', '!=', 1],
                         ['OrgStat2', '=', $student],
                         ['org-person.orgID', '=', $this->currentPerson->defaultOrgID]
                     ])->count();
        });
        $retiree             = 'Retiree';
        $retirees            = Cache::get('retiree_data', function() {
            $retiree = 'Retiree';
            return DB::table('person')
                     ->join('org-person', 'org-person.personID', '=', 'person.personID')
                     ->where([
                         ['person.personID', '!=', 1],
                         ['OrgStat2', '=', $retiree],
                         ['org-person.orgID', '=', $this->currentPerson->defaultOrgID]
                     ])->count();
        });

        array_push($topBits, [3, 'Total People', $total_people, '', '', '']);
        array_push($topBits, [3, $individual . " Members", $individuals, '', '', '']);
        array_push($topBits, [3, $retiree . " Members", $retirees, '', '', '']);
        array_push($topBits, [3, $student . " Members", $students, '', '', '']);

        $mbr_list = Cache::get('mbr_list', function() {
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

    // Shows profile information for chosen person (or self)
    public function show ($id) {
        // responds to GET /profile/{id}
        $this->currentPerson = Person::where('personID', '=', auth()->user()->id)->with('socialites')->first();
        if($id == 'my') {
            // set $id to the logged in Person, otherwise keep the $id given
            $id = $this->currentPerson->personID;
            if(request()->query() != null) {
                if($this->currentPerson->avatarURL === null
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

        $profile = Person::where('person.personID', $id)
                         ->join('users as u', 'u.id', '=', 'person.personID')
                         ->join('org-person as op', function($join) {
                             $join->on('op.personID', '=', 'person.personID');
                             $join->on('op.orgID', '=', 'person.defaultOrgID');
                         })
                         ->join('organization as o', 'o.orgID', '=', 'person.defaultOrgID')
                         ->select(DB::raw("prefix, firstName, midName, lastName, suffix, prefName, u.login, title, compName, indName,
                OrgStat1, OrgStat2, OrgStat3, OrgStat4, OrgStat5, OrgStat6, OrgStat7, OrgStat8, OrgStat9, OrgStat10,
                date_format(RelDate1, '%c/%e/%Y') as RelDate1, date_format(RelDate2, '%c/%e/%Y') as RelDate2, date_format(RelDate3, '%c/%e/%Y') as RelDate3,
                    date_format(RelDate4, '%c/%e/%Y') as RelDate4, date_format(RelDate5, '%c/%e/%Y') as RelDate5, date_format(RelDate6, '%c/%e/%Y') as RelDate6, 
                    date_format(RelDate7, '%c/%e/%Y') as RelDate7, date_format(RelDate8, '%c/%e/%Y') as RelDate8, date_format(RelDate9, '%c/%e/%Y') as RelDate9, 
                    date_format(RelDate10, '%c/%e/%Y') as RelDate10,
                    OSN1, OSN2, OSN3, OSN4, OSN5, OSN6, OSN7, OSN8, OSN9, OSN10, 
                    ODN1, ODN2, ODN3, ODN4, ODN5, ODN6, RelDate7, ODN8, ODN9, ODN10, person.personID"))->first();

        $topBits = '';

        $prefixes = DB::table('prefixes')->get();

        $industries = DB::table('industries')->get();
        $addrTypes  = DB::table('address-type')->get();
        $emailTypes = DB::table('email-type')->get();
        $phoneTypes = DB::table('phone-type')->get();

        $addresses =
            Address::where('personID', $id)->select('addrID', 'addrTYPE', 'addr1', 'addr2', 'city', 'state', 'zip', 'cntryID')->get();
        $countries = DB::table('countries')->select('cntryID', 'cntryName')->get();

        $emails =
            Email::where('personID', $id)->select('emailID', 'emailTYPE', 'emailADDR', 'isPrimary')->orderBy('isPrimary', 'DESC')->get();

        $phones =
            Phone::where('personID', $id)->select('phoneID', 'phoneType', 'phoneNumber')->get();

        return view('v1.auth_pages.members.profile',
            compact('profile', 'topBits', 'prefixes', 'industries', 'addresses', 'emails',
                'addrTypes', 'emailTypes', 'countries', 'phones', 'phoneTypes'));
    }

    public function create () {
        // responds to /blah/create and shows add/edit form
    }

    public function store (Request $request) {
        // responds to POST to /blah and creates, adds, stores the event
        dd(request()->all());
    }

    public function update (Request $request, $id) {
        // responds to PATCH /blah/id
        $personID = request()->input('pk');

        $name = request()->input('name');
        if(strpos($name, '-')) {
            // if passed from the registration receipt, the $name will have a dash
            list($name, $field) = array_pad(explode("-", $name, 2), 2, null);
        }
        $value = request()->input('value');

        if($name == 'login') {

            // when changing login we need to:
            // 1. update user->login, user->email, and person->login with the new value

            $user        = User::find($id);
            $orig_email  = $user->login;
            $user->login = $value;
            $user->email = $value;
            $user->save();

            $person            = Person::find($id);

            // 2. trigger a notification to be sent to the old email address
            $person->notify(new LoginChange($person, $orig_email));

            $person->login     = $value;
            $person->updaterID = auth()->user()->id;
            $person->save();

            $orig            = Email::where('emailADDR', '=', $orig_email)->first();
            $orig->isPrimary = 0;
            $orig->save();

            // 3. change the Email::isPrimary field on the old and new primary email (the one where email will be sent)
            $new_email      = $value;
            $new            = Email::where('emailADDR', '=', $new_email)->first();
            $new->isPrimary = 1;
            $new->save();

        } else {
            $person            = Person::find($id);
            $person->{$name}   = $value;
            $person->updaterID = auth()->user()->id;
            $person->save();
        }
    }

    public function undo_login (Person $person, $string) {
        $email = decrypt($string);
        $user = User::find($person->personID);
        $user->login = $email;
        $user->email = $email;
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

        $header = "Success";
        $message = "Your login was successfully changed back to $email.  A confirmation email has been sent.";
        return view('v1.public_pages.thanks', compact('header', 'message'));
    }

    public function change_password (Request $request) {
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
        if(Hash::check($curPass, $user->password)){
            // update password
            $user->password = bcrypt($password);
            $user->save();
            request()->session()->flash('alert-success', "The password was changed successfully.");

            // send notification
            $person->notify(new PasswordChange($person));
            request()->session()->flash('alert-info', "A confirmation email was sent to $person->login.");

            return back()->withInput(['tab' => 'tab_content2']);
        } else {
            request()->session()->flash('alert-danger', "Current password did not match.");
            return back()
                ->withErrors($validator)
                ->withInput(['tab' => 'tab_content2']);
        }
    }

    /**
     * Redirect the user to the LinkedIn authentication page.
     *
     * @return Response
     */
    public function redirectToLinkedIn () {
        return Socialite::driver('linkedin')->redirect();
    }

    /**
     * Obtain the user information from LinkedIn
     *
     * @return Response
     */
    public function handleLinkedInCallback () {
        $user = Socialite::driver('linkedin')->user();
    }

    public function show_report(){
        $topBits = '';
        return view('v1.auth_pages.members.mbr_report', compact('topBits'));
    }
}

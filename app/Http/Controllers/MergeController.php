<?php

namespace App\Http\Controllers;

use App\Address;
use App\Email;
use App\OrgPerson;
use App\PersonSocialite;
use App\RegFinance;
use App\Registration;
use App\User;
use Illuminate\Http\Request;
use App\Person;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\DB;

class MergeController extends Controller
{

    protected $models;

    public function __construct () {
        $this->middleware('auth');

        $this->models = [
            'o' => 'Org',
            'p' => 'Person',
            'e' => 'Event',
        ];
    }

    /*
     * showMerge function
     * @param: $letter
     * @param: $id1?
     * @param: $id2?
     *
     * 1. If no $id given, give a way to select one (by PMI ID, or personID, lastName
     * 2. Once we're working with a $person, give a way to see the potential merges
     * 3. With merge candidates selected, give the ability to select what wins
     *    think about potentially different merge scenarios
     * 4. Also merge the associated user records
     *
     */
    public function show ($letter, $id1 = null, $id2 = null) {
        $collection          = null;
        $model1              = null;
        $model2              = null;
        $this->currentPerson = Person::find(auth()->user()->id);
        if($letter === null) {
            // go to a blank merge page
        }

        $class = 'App\\' . $this->models[$letter];

        if(class_exists($class)) {
            if($id1 === null || $id2 === null) {
                // show a quick form to pick from the chosen class
                switch ($letter) {
                    case 'p':
                        $collection = $class::whereHas('orgs', function($q) {
                            $q->where('organization.orgID', '=', $this->currentPerson->defaultOrgID);
                        })
                                            ->where([
                                                ['personID', '!=', 1]
                                            ])->get();
                        break;
                }
            }
            if($id1 !== null) {
                switch ($letter) {
                    case 'p':
                        $model1 = $class::find($id1);
                        break;
                }
            }

            if($id2 !== null) {
                switch ($letter) {
                    case 'p':
                        $model2 = $class::find($id2);
                        break;
                }
            }
        } else {
            // class doesn't exist so... ?
        }
        return view('v1.auth_pages.organization.merge', compact('model1', 'letter', 'collection', 'model2'));
    }

    /*
     * getModel: gets model specified by $letter -> class
     * @param: $letter
     * @param: $model1
     * @param: $model2 (sometimes)
     *
     */
    public function getmodel (Request $request, $letter) {

        $this->currentPerson = Person::find(auth()->user()->id);
        $class               = 'App\\' . $this->models[$letter];
        $model1              = request()->input('model1');
        $model2              = request()->input('model2');

        if(isset($model1) && !is_numeric($model1)) {
            list($id1, $field) = array_pad(explode("-", $model1, 2), 2, null);
        } else {
            $id1 = $model1;
        }

        if(isset($model2) && !is_numeric($model2)) {
            list($id2, $field) = array_pad(explode("-", $model2, 2), 2, null);
        } else {
            $id2 = $model2;
        }

        if(class_exists($class)) {
            if(isset($id1)) {
                $model1 = $class::with('orgperson', 'emails')->where('personID', $id1)->first();
            }
            if(isset($id2)) {
                $model2 = $class::with('orgperson', 'emails')->where('personID', $id2)->first();
            }
        }

        $string = "/merge/" . $letter . "/" . $model1->personID;
        if(isset($model2)) {
            $string .= "/" . $model2->personID;
        }
        return redirect($string);
    }

    /*
     * Just like getmodel, for the group-registration page
     * @param: string
     */
    public function getperson (Request $request) {
        $string = request()->input('string');
        list($personID, $field) = array_pad(explode("-", $string, 2), 2, null);
        $person = Person::with('orgperson')
                        ->where('personID', $personID)
                        ->first();
        return json_encode(array('status' => 'success',
            'personID' => $person->personID,
            'firstName' => $person->firstName,
            'lastName' => $person->lastName,
            'login' => $person->login,
            'OrgStat1' => $person->orgperson->OrgStat1,
        ));
    }

    /*
    public function find (Request $request) {
        return Person::search($request->get('q'))->with('emails', 'orgperson')->get();
    }
    */

    /*
     * store function
     * @param: $letter
     * @param: $model1
     * @param: $model2
     * @param: $ignore_array
     *
     * 1. $model1 survives;
     * 2. foreach $column variable with a 2, take the $model2 value
     * 3. update $model1->updaterID
     * 4. Take all emails, addresses, reg->regID, rf->regID on $model2 and move to $model1
     * 5. Remove duplicates:  $model2, $orgPerson, $user
     *
     */
    public function store (Request $request) {
        // responds to POST to /execute_merge
        $this->currentPerson = Person::find(auth()->user()->id);
        $letter              = request()->input('letter');

        $id1 = request()->input('model1');
        $id2 = request()->input('model2');

        // add phone numbers
        $model1 = Person::where('personID', $id1)
                        ->with('orgperson', 'emails', 'addresses', 'registrations', 'socialites', 'regfinances')
                        ->first();

        // add phone numbers
        $model2 = Person::where('personID', $id2)
                        ->with('orgperson', 'emails', 'addresses', 'registrations', 'socialites', 'regfinances')
                        ->first();

        $columns      = explode(',', request()->input('columns'));
        $ignore_array = explode(',', request()->input('ignore_array'));

        foreach($columns as $c) {
            $x = request()->input($c);
            if($x == 2) {
                $model1->$c = $model2->$c;
            }
        }
        $model1->updaterID = $this->currentPerson->personID;

        foreach($model2->emails as $e) {
            $m2            = Email::find($e->emailID);
            $m2->personID  = $model1->personID;
            $m2->isPrimary = 0;
            $m2->updaterID = $this->currentPerson->personID;
            $m2->save();
        }
        foreach($model2->addresses as $e) {
            $m2            = Address::find($e->addrID);
            $m2->personID  = $model1->personID;
            $m2->updaterID = $this->currentPerson->personID;
            $m2->save();
        }
        foreach($model2->socialites as $e) {
            $e->personID  = $model1->personID;
            $e->updaterID = $this->currentPerson->personID;
            $e->save();
        }
        foreach($model2->registrations as $e) {
            $m2            = Registration::find($e->regID);
            $m2->personID  = $model1->personID;
            $m2->updaterID = $this->currentPerson->personID;
            $m2->save();
        }
        foreach($model2->regfinances as $e) {
            $m2            = RegFinance::find($e->regID);
            $m2->personID  = $model1->personID;
            // Person soft-deletes require unique key 'login' to be uniquely modified
            $m2->login     = '_' . $m2->login;
            $m2->updaterID = $this->currentPerson->personID;
            $m2->save();
        }
        $model1->save();

        $o = OrgPerson::where([
            ['personID', $model2->personID],
            ['orgID', $this->currentPerson->defaultOrgID]
        ])->first();

        if(isset($o)) {
            $o->delete();
        }

        // change any permissions that might be set
        DB::statement("update role_user set user_id = $model1->personID where user_id = $model2->personID");

        request()->session()->flash('alert-success', $this->models[$letter] .
            " record: " . $model2->personID . " was successfully merged into " . $model1->personID . '.');

        $u = User::find($model2->personID);
        if(isset($u)) {
            $u->delete();
        }

        // Trigger Notification
        // Need to notify $model2 it's being merged ONLY if password !== null
        // $model2->notify(new AccountMerge($model2, $model1));
        $model2->delete();

        return redirect('/merge/' . $letter . '/' . $model1->personID);
    }

    public function index () {
        return view('v1.auth_pages.organization.merge');
    }

    /*
     * query function - the function that drives the typeahead field search/response
     * @param: $request
     */
    public function query (Request $request) {
        $this->currentPerson = Person::find(auth()->user()->id);
        $query               = $request->q;
        // jerry-rigging to make work as a get or post
        if(!isset($query)) {
            $query = request()->input('query');
        }
        $exclude_model = $request->m;
        $usersArray    = [];

        if($exclude_model) {
            $res = Person::where('personID', '<>', $exclude_model)
                         ->where(function($q) use ($query) {
                             $q->where('firstName', 'LIKE', "%$query%")
                               ->orWhere('personID', 'LIKE', "%$query%")
                               ->orWhere('lastName', 'LIKE', "%$query%")
                               ->orWhere('login', 'LIKE', "%$query%")
                               ->orWhere('personID', 'LIKE', "%$query%")
                               ->orWhereHas('orgperson', function($q) use ($query) {
                                   $q->where('OrgStat1', 'LIKE', "%$query%");
                               })
                               ->orWhereHas('emails', function($q) use ($query) {
                                   $q->where('emailADDR', 'LIKE', "%$query%");
                               });
                         })
                // moved outside of where clause above because this is and-ed
                         ->whereHas('orgs', function($q) {
                    $q->where('organization.orgID', '=', $this->currentPerson->defaultOrgID);
                })
                         ->with('orgperson')
                         ->select('personID', 'firstName', 'lastName', 'login')
                         ->get();
        } else {
            $res = Person::where('firstName', 'LIKE', "%$query%")
                         ->orWhere('personID', 'LIKE', "%$query%")
                         ->orWhere('lastName', 'LIKE', "%$query%")
                         ->orWhere('login', 'LIKE', "%$query%")
                         ->orWhereHas('orgperson', function($q) use ($query) {
                             $q->where('OrgStat1', 'LIKE', "%$query%");
                         })
                         ->orWhereHas('emails', function($q) use ($query) {
                             $q->where('emailADDR', 'LIKE', "%$query%");
                         })
                         ->whereHas('orgs', function($q) {
                             $q->where('organization.orgID', '=', $this->currentPerson->defaultOrgID);
                         })
                         ->with('orgperson')
                         ->select('personID', 'firstName', 'lastName', 'login')
                         ->get();
        }

        foreach($res as $index => $p) {
            $usersArray[$index] = ['id' => $p->personID,
                'value' => $p->personID . "-" . $p->firstName . " " . $p->lastName . ": " . $p->login];
        }
        return response()->json($usersArray);
    }
}

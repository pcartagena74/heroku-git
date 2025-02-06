<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Email;
use App\Models\Event;
use App\Models\Location;
use App\Models\OrgPerson;
use App\Models\Person;
use App\Models\Phone;
use App\Models\RegFinance;
use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use App\Notifications\AccountMerge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Kordy\Ticketit\Models\Ticket;

class MergeController extends Controller
{
    protected $models;

    public function __construct()
    {
        $this->middleware('auth', ['except' => ['query']]);

        $this->models = [
            'o' => 'Org',
            'p' => 'Person',
            'e' => 'Event',
            'l' => 'Location',
        ];
    }

    /**
     * show function
     *
     * @param  null  $id1
     * @param  null  $id2
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     * 1. If no $id given, give a way to select one (by PMI ID, or personID, lastName
     * 2. Once we're working with a $person, give a way to see the potential merges
     * 3. With merge candidates selected, give the ability to select what wins
     *    think about potentially different merge scenarios
     * 4. Also merge the associated user records
     */
    public function show($letter, $id1 = null, $id2 = null): View
    {
        $collection = null;
        $model1 = null;
        $model2 = null;
        $this->currentPerson = Person::find(auth()->user()->id);
        if ($letter === null) {
            // go to a blank merge page
        }

        $class = 'App\\Models\\'.$this->models[$letter];

        if (class_exists($class)) {
            if ($id1 === null || $id2 === null) {
                // show a quick form to pick from the chosen class
                switch ($letter) {
                    case 'p':
                        $collection = $class::whereHas('orgs', function ($q) {
                            $q->where('organization.orgID', '=', $this->currentPerson->defaultOrgID);
                        })
                            ->where([
                                ['personID', '!=', 1],
                            ])->get();
                        break;
                    case 'l':
                        $collection = $class::where('orgID', '=', $this->currentPerson->defaultOrgID)->get();
                        break;
                }
            }
            if ($id1 !== null) {
                $model1 = $class::find($id1);
            }

            if ($id2 !== null) {
                $model2 = $class::find($id2);
            }
        } else {
            // class doesn't exist so... ?
        }

        return view('v1.auth_pages.organization.merge', compact('model1', 'letter', 'collection', 'model2'));
    }

    /**
     * getModel: gets model(s) specified by $letter -> class
     *           This is used to build the merge form that is displayed for whatever models will be merged.
     *
     * @param: $class as represented by $letter
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Laravel\Lumen\Http\Redirector : $string
     */
    public function getmodel(Request $request, $letter): RedirectResponse
    {
        $this->currentPerson = Person::find(auth()->user()->id);
        $class = 'App\\Models\\'.$this->models[$letter];
        $model1 = request()->input('model1');
        $model2 = request()->input('model2');

        if (isset($model1) && ! is_numeric($model1)) {
            [$id1, $field] = array_pad(explode('-', $model1, 2), 2, null);
        } else {
            $id1 = $model1;
        }

        if (isset($model2) && ! is_numeric($model2)) {
            [$id2, $field] = array_pad(explode('-', $model2, 2), 2, null);
        } else {
            $id2 = $model2;
        }

        if (class_exists($class)) {
            switch ($letter) {
                case 'p':
                    if (isset($id1)) {
                        $model1 = $class::with('orgperson', 'emails')->where('personID', $id1)->first();
                    }
                    if (isset($id2)) {
                        $model2 = $class::with('orgperson', 'emails')->where('personID', $id2)->first();
                    }
                    break;

                case 'l':
                    if (isset($id1)) {
                        $model1 = $class::where('locID', $id1)->first();
                    }
                    if (isset($id2)) {
                        $model2 = $class::where('locID', $id2)->first();
                    }

                    break;
            }
        }

        switch ($letter) {
            case 'p':
                $string = '/merge/'.$letter.'/'.$model1->personID;
                if (isset($model2)) {
                    $string .= '/'.$model2->personID;
                }
                break;

            case 'l':
                $string = '/merge/'.$letter.'/'.$model1->locID;
                if (isset($model2)) {
                    $string .= '/'.$model2->locID;
                }
                break;
        }

        return redirect($string);
    }

    /**
     * Just like getmodel, for the group-registration page
     *
     * @param: string
     *
     * @return: json entry
     */
    public function getperson(Request $request)
    {
        $string = request()->input('string');
        [$personID, $field] = array_pad(explode('-', $string, 2), 2, null);
        $person = Person::with('orgperson')->find($personID);

        if ($person !== null) {
            return json_encode(['status' => 'success',
                'p' => $person,
                'personID' => $person->personID,
                'firstName' => $person->firstName,
                'lastName' => $person->lastName,
                'login' => $person->login,
                'OrgStat1' => $person->orgStat1(),
            ]);
        }
    }

    /**
     * store function for surviving model.  Should probably be update
     *
     * @param: $letter
     *
     * @param: $model1
     *
     * @param: $model2
     *
     * @param: $ignore_array
     *
     * 1. $model1 survives;
     * 2. foreach $column variable with a 2, take the $model2 value
     * 3. update $model1->updaterID
     * 4. Take all emails, addresses, reg->regID, rf->regID on $model2 and move to $model1
     * 4b. Change the model2->person->login to "merged_$login" to avoid key conflicts
     * 4c. Change user_id in ticketit as appropriate
     * 5. Remove duplicates:  $model2, $orgPerson, $user
     */
    public function store(Request $request)
    {

        // responds to POST to /execute_merge
        $this->currentPerson = Person::find(auth()->user()->id);
        $letter = request()->input('letter');
        $return_model = null;

        $id1 = request()->input('model1');
        $id2 = request()->input('model2');

        switch ($letter) {
            // Switch to setup model1 & model2
            case 'p':
                // add phone numbers
                $model1 = Person::where('personID', $id1)
                    ->with('orgperson', 'emails', 'addresses', 'registrations', 'socialites', 'regfinances', 'phones')
                    ->first();

                // add phone numbers
                $model2 = Person::where('personID', $id2)
                    ->with('orgperson', 'emails', 'addresses', 'registrations', 'socialites', 'regfinances', 'phones')
                    ->first();
                break;

            case 'l':
                $model1 = Location::find($id1);
                $model2 = Location::find($id2);
                if ($model2->locNote !== null) {
                    $model1->locNote .= $model2->locNote;
                }
                break;
        }

        $columns = explode(',', request()->input('columns'));
        $ignore_array = explode(',', request()->input('ignore_array'));

        foreach ($columns as $c) {
            $x = request()->input($c);
            if ($x == 2) {
                $model1->$c = $model2->$c;
            }
        }

        DB::beginTransaction();
        try {
            $model1->updaterID = $this->currentPerson->personID;
            $model1->save();

            $model2->updaterID = $this->currentPerson->personID;
            $model2->save();
            $model2->delete();

            switch ($letter) {
                // Switch to handle relation models associated with the target models
                case 'p':
                    // Find all emails, addresses, registrations, etc. associated with model2 location and update

                    foreach ($model2->emails as $e) {
                        $m2 = Email::find($e->emailID);
                        $m2->personID = $model1->personID;
                        $m2->isPrimary = 0;
                        $m2->updaterID = $this->currentPerson->personID;
                        $m2->save();
                    }
                    foreach ($model2->addresses as $e) {
                        $m2 = Address::find($e->addrID);
                        $m2->personID = $model1->personID;
                        $m2->updaterID = $this->currentPerson->personID;
                        $m2->save();
                    }
                    foreach ($model2->socialites as $e) {
                        $e->personID = $model1->personID;
                        $e->updaterID = $this->currentPerson->personID;
                        $e->save();
                    }
                    foreach ($model2->registrations as $e) {
                        $m2 = Registration::find($e->regID);
                        $m2->personID = $model1->personID;
                        $m2->updaterID = $this->currentPerson->personID;
                        $m2->save();
                    }
                    foreach ($model2->regfinances as $e) {
                        $m2 = RegFinance::find($e->regID);
                        $m2->personID = $model1->personID;
                        $m2->updaterID = $this->currentPerson->personID;
                        $m2->save();
                    }
                    foreach ($model2->phones as $e) {
                        $m2 = Phone::find($e->phoneID);
                        $m2->personID = $model1->personID;
                        $m2->updaterID = $this->currentPerson->personID;
                        $m2->save();
                    }
                    // on the off chance $m2 owns TicketIt tickets...
                    foreach ($model2->user->tickets as $t) {
                        $m2 = Ticket::find($t->id);
                        $m2->user_id = $model1->personID;
                        $m2->save();
                    }

                    $o1 = OrgPerson::where([
                        ['personID', $model1->personID],
                        ['orgID', $this->currentPerson->defaultOrgID],
                    ])->first();

                    $o2 = OrgPerson::where([
                        ['personID', $model2->personID],
                        ['orgID', $this->currentPerson->defaultOrgID],
                    ])->first();

                    // if OrgStat1 (pmi_id) is set in either orgperson record, it will survive.
                    // if there happen to be 2 OrgStat1 values, the "keeper's" OrgStat1 survives.
                    if (($o1->OrgStat1 === null || $o1->OrgStat1 == '') && isset($o2)) {
                        if ($o2->OrgStat1) {
                            $o1->OrgStat1 = $o2->OrgStat1;
                            $o1->OrgStat2 = $o2->OrgStat2;
                            $o1->RelDate1 = $o2->RelDate1;
                            $o1->RelDate2 = $o2->RelDate2;
                            $o1->RelDate3 = $o2->RelDate3;
                            $o1->RelDate4 = $o2->RelDate4;
                            $o1->save();
                        }
                    }
                    if (isset($o2)) {
                        $o2->delete();
                    }

                    $u1 = User::find($model1->personID);
                    $u2 = User::find($model2->personID);

                    // change any permissions that might be set
                    // DB::statement("update role_user set user_id = $model1->personID where user_id = $model2->personID");

                    $weedout = $u1->roles()->pluck('id')->toArray();
                    foreach ($u2->roles as $r) {
                        if (! in_array($r->id, $weedout)) {
                            $u1->roles()->attach($r->id);
                        }
                        $u2->roles()->detach($r->id);
                    }

                    request()->session()->flash('alert-success', trans(
                        'messages.messages.merge_succ',
                        ['model' => $this->models[$letter], 'record1' => $model2->personID,
                            'record2' => $model1->personID, ]
                    ));

                    // If a password is set for a user record that will not survive and survivor password is null, copy it.
                    // Then delete non-surviving user record

                    if ($u2 !== null) {
                        if ($u1->password === null) {
                            if ($u2->password !== null) {
                                $u1->password = $u2->password;
                                // Need to notify $model2 it's being merged ONLY if password !== null
                                $model2->notify(new AccountMerge($model1, $model2));
                            }
                        }
                        $u2->delete();
                        $u1->save();
                    }

                    // Person soft-deletes require unique key 'login' to be uniquely modified
                    $model2->login = "merged_$model1->personID".'_'.$model2->login;
                    $model2->save();
                    $model2->delete();

                    $return_model = $model1->personID;
                    break;

                case 'l':
                    // Find all events with model2's location and update
                    $events = Event::where([
                        ['orgID', '=', $this->currentPerson->defaultOrgID],
                        ['locationID', '=', $model2->locID],
                    ])->get();

                    $cnt = 0;
                    foreach ($events as $e) {
                        $e->locationID = $model1->locID;
                        $e->updaterID = $this->currentPerson->personID;
                        $e->save();
                        $cnt++;
                    }
                    request()->session()->flash('alert-warning', trans(
                        'messages.messages.loc_merge',
                        ['id' => $model1->locID, 'id2' => $model2->locID, 'count' => $cnt]
                    ));
                    $return_model = $model1->locID;
                    break;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            request()->session()->flash('alert-warning', trans('messages.flashes.merge_failure', ['e' => $e]));

            return back()->withInput();
        }

        return redirect('/merge/'.$letter.'/'.$return_model);
    }

    public function index(): View
    {
        return view('v1.auth_pages.organization.merge');
    }

    /**
     * query function - the function that drives the typeahead field search/response
     */
    public function query(Request $request): JsonResponse
    {
        $this->currentPerson = Person::find(auth()->user()->id);
        $query = $request->q;
        $letter = $request->l;
        // jerry-rigging to make work as a get or post
        if (! isset($query)) {
            $query = request()->input('query');
        }
        $exclude_model = $request->m;
        $usersArray = [];
        $locArray = [];

        if ($exclude_model) {
            switch ($letter) {
                case 'p':
                    $res = Person::where('personID', '<>', $exclude_model)
                        ->where(function ($q) use ($query) {
                            $q->where('firstName', 'LIKE', "%$query%")
                                ->orWhere('personID', 'LIKE', "%$query%")
                                ->orWhere('lastName', 'LIKE', "%$query%")
                                ->orWhere('login', 'LIKE', "%$query%")
                                ->orWhere('personID', 'LIKE', "%$query%")
                                ->orWhereHas('orgperson', function ($q) use ($query) {
                                    $q->where('OrgStat1', 'LIKE', "%$query%");
                                })
                                ->orWhereHas('emails', function ($q) use ($query) {
                                    $q->where('emailADDR', 'LIKE', "%$query%");
                                });
                        })
                        // moved outside of where clause above because this is and-ed
                        ->whereHas('orgs', function ($q) {
                            $q->where('organization.orgID', '=', $this->currentPerson->defaultOrgID);
                        })
                        ->with('orgperson')
                        ->select('personID', 'firstName', 'lastName', 'login')
                        ->get();

                    foreach ($res as $index => $p) {
                        $usersArray[$index] = ['id' => $p->personID,
                            'value' => $p->personID.'-'.$p->firstName.' '.$p->lastName.': '.$p->login, ];
                    }

                    return response()->json($usersArray);
                    break;

                case 'l':
                    $res = Location::where([
                        ['orgID', '=', $this->currentPerson->defaultOrgID],
                        ['locID', '<>', $exclude_model],
                    ])->where(function ($q) use ($query) {
                        $q->where('locName', 'LIKE', "%$query%")
                            ->orWhere('addr1', 'LIKE', "%$query%")
                            ->orWhere('addr2', 'LIKE', "%$query%")
                            ->orWhere('city', 'LIKE', "%$query%");
                    })->get();

                    foreach ($res as $index => $l) {
                        $locArray[$index] = ['id' => $l->locID,
                            'value' => $l->locID.'-'.$l->locName.' '.$l->addr1, ];
                    }

                    return response()->json($locArray);
                    break;
            }
        } else {
            switch ($letter) {
                case 'p':
                    $res = Person::where('firstName', 'LIKE', "%$query%")
                        ->orWhere('personID', 'LIKE', "%$query%")
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
                        ->with('orgperson')
                        ->select('personID', 'firstName', 'lastName', 'login')
                        ->get();

                    foreach ($res as $index => $p) {
                        $usersArray[$index] = ['id' => $p->personID,
                            'value' => $p->personID.'-'.$p->firstName.' '.$p->lastName.': '.$p->login, ];
                    }

                    return response()->json($usersArray);
                    break;

                case 'l':
                    $res = Location::where('orgID', '=', $this->currentPerson->defaultOrgID)
                        ->where(function ($q) use ($query) {
                            $q->where('locName', 'LIKE', "%$query%")
                                ->orWhere('addr1', 'LIKE', "%$query%")
                                ->orWhere('addr2', 'LIKE', "%$query%")
                                ->orWhere('city', 'LIKE', "%$query%");
                        })->get();

                    foreach ($res as $index => $l) {
                        $locArray[$index] = ['id' => $l->locID,
                            'value' => $l->locID.'-'.$l->locName.' '.$l->addr1, ];
                    }

                    return response()->json($locArray);
                    break;
            }
        }
    }
}

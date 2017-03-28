<?php

namespace App\Http\Controllers;

use App\Person;
use App\OrgPerson;
use App\Registration;
use App\User;
use Illuminate\Http\Request;
use App\Ticket;
use App\Event;
use App\Email;
use App\RegFinance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class RegistrationController extends Controller
{
    public function __construct () {
        $this->middleware('auth', ['except' => ['showRegForm', 'store']]);
    }

    public function index () {
        // responds to /blah
    }

    public function showRegForm (Request $request, $id) {
        // Registering for an event from /event/{id}
        $ticket        = Ticket::find(request()->input('ticketID'));
        $quantity      = request()->input('quantity');
        $discount_code = request()->input('discount_code');
        $event         = Event::find($ticket->eventID);
        if($event->hasFood) {
            return view('v1.public_pages.register', compact('ticket', 'event', 'quantity', 'discount_code'));
        } else {
            return view('v1.public_pages.register-no-food', compact('ticket', 'event', 'quantity', 'discount_code'));
        }
    }

    public function show ($id) {
        // responds to GET /blah/id
    }

    public function create () {
        // responds to /blah/create and shows add/edit form
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store (Request $request) {
        // responds to POST to /blah and creates, adds, stores the event
        // check if someone logged in
        // if so, get person record and update
        // if not, check to see if email matches any person record and update
        // else add a person record and email record
        // record registration_form1 answers
        // LOOP if quantity > 1 and add new person records avoiding duplicates as possible
        // display registration_form2

        $event    = Event::find(request()->input('eventID'));
        $resubmit = Registration::where('token', request()->input('_token'))->first();
        if(Auth::check()) {
            $this->currentPerson = Person::find(auth()->user()->id);
            $this->currentPerson->load('orgperson');
        }

        // This is a quick check to pass through without saving another record if the _token is already in the db
        if(count($resubmit) > 0) {
            return redirect('/confirm_registration/' . $resubmit->regID);
        }
//dd(request()->all());
        $checkEmail = request()->input('login');

        $prefix        = request()->input('prefix');
        $firstName     = request()->input('firstName');
        $middleName    = request()->input('middleName');
        $lastName      = request()->input('lastName');
        $suffix        = request()->input('suffix');
        $prefName      = request()->input('prefName');
        $compName      = request()->input('compName');
        $indName       = request()->input('indName');
        $title         = request()->input('title');
        $eventQuestion = request()->input('eventQuestion');
        $eventTopics   = request()->input('eventTopics');
        $affiliation   = request()->input('affiliation');
        $quantity      = request()->input('quantity');
        $flatamt       = request()->input('flatamt');
        $percent       = request()->input('percent');
        $dCode         = request()->input('discount_code');
        $ticketID      = request()->input('ticketID');
        $subtotal      = request()->input('sub1');
        $origcost      = request()->input('cost1');
        if($event->hasFood) {
            $specialNeeds = request()->input('specialNeeds');
            $eventNotes   = request()->input('eventNotes');
            $allergenInfo = request()->input('allergenInfo');
            $cityState    = request()->input('cityState');
            //$allergenInfo = implode(",", request()->input('allergenInfo'));
        }

        // put in some validation to ensure that nothing was tampered with
        $total = request()->input('total');

        $subcheck = $subtotal;

        $email = Email::where('emailADDR', $checkEmail)->first();

        if(!Auth::check() && $email === null) {
            // Not logged in and email is not in database; must create
            $person               = new Person;
            $person->prefix       = $prefix;
            $person->firstName    = $firstName;
            $person->midName      = $middleName;
            $person->lastName     = $lastName;
            $person->suffix       = $suffix;
            $person->defaultOrgID = $event->orgID;
            $person->prefName     = $prefName;
            $person->compName     = $compName;
            $person->indName      = $indName;
            $person->title        = $title;
            $person->login        = $checkEmail;
            if($event->hasFood) {
                $person->allergenInfo = implode(",", $allergenInfo);
            }
            $person->affiliation = implode(",", $affiliation);
            $person->save();

            // Need to create a user record with new personID

            $user = new User();
            $user->id = $person->personID;
            $user->login = $checkEmail;
            $user->email = $checkEmail;
            $user->save();
            $user->login();


            $op           = new OrgPerson;
            $op->orgID    = $event->orgID;
            $op->personID = $person->personID;
            $op->save();

            $email            = new Email;
            $email->personID  = $person->personID;
            $email->emailADDR = $checkEmail;
            $email->isPrimary = 1;
            $email->save();

            $regBy  = $person->firstName . " " . $person->lastName;
            $regMem = 'Non-Member';

        } elseif(!Auth::check() && $email !== null) {
            // Not logged in and email is in the database;
            // Should force a login -- return to form with input saved.
            //dd($affiliation);
            //flash("alert-warning", "You have an account that we've created for you. Please attempt to login and we'll send you a password to your email address.");
            //dd('No one logged in but main email is in DB');
            request()->session()->flash('alert-warning',
                "You have an account that we've created for you. Please click the login button. 
             If you haven't yet set a password, we'll send one to your email address.");
            return back()->withInput();

        } elseif(Auth::check() && ($email->personID == $this->currentPerson->personID)) {
            // the email entered belongs to the person logged in; ergo in DB
            $person         = $this->currentPerson;
            $person->prefix = $prefix;
            //$person->firstName    = $firstName;   // this doesn't get edited
            $person->midName = $middleName;
            //$person->lastName     = $lastName;    // this doesn't get edited
            $person->suffix       = $suffix;
            $person->defaultOrgID = $event->orgID;
            $person->prefName     = $prefName;
            $person->compName     = $compName;
            $person->indName      = $indName;
            $person->title        = $title;
            if($event->hasFood) {
                $person->allergenInfo = implode(",", $allergenInfo);
            }
            $person->affiliation = implode(",", $affiliation);
            $person->save();

            $regBy = $person->firstName . " " . $person->lastName;
            if($person->orgperson->OrgStat1 === null) {
                $regMem = 'Non-Member';
            } else {
                $regMem = 'Member';
            }

        } elseif(Auth::check() && ($email->personID != $this->currentPerson->personID)) {
            // someone logged in is registering for someone else in the DB (usually CAMI)
            $person         = Person::find($email->personID);
            $person->prefix = $prefix;
            //$person->firstName    = $firstName;   // this doesn't get edited
            $person->midName = $middleName;
            //$person->lastName     = $lastName;    // this doesn't get edited
            $person->suffix       = $suffix;
            $person->defaultOrgID = $event->orgID;
            $person->prefName     = $prefName;
            $person->compName     = $compName;
            $person->indName      = $indName;
            $person->title        = $title;
            if($event->hasFood) {
                $person->allergenInfo = implode(",", $allergenInfo);
            }
            $person->affiliation = implode(",", $affiliation);
            $person->save();

            $regBy = $this->currentPerson->firstName . " " . $this->currentPerson->lastName;
            if($person->orgperson->OrgStat1 === null) {
                $regMem = 'Non-Member';
            } else {
                $regMem = 'Member';
            }

        } else {
            // someone logged in is registering for someone else NOT in the DB
            $person               = new Person;
            $person->prefix       = $prefix;
            $person->firstName    = $firstName;
            $person->midName      = $middleName;
            $person->lastName     = $lastName;
            $person->suffix       = $suffix;
            $person->defaultOrgID = $event->orgID;
            $person->prefName     = $prefName;
            $person->compName     = $compName;
            $person->indName      = $indName;
            $person->title        = $title;
            if($event->hasFood) {
                $person->allergenInfo = implode(",", $allergenInfo);
            }
            $person->affiliation = implode(",", $affiliation);
            $person->creatorID   = $this->currentPerson->personID;
            $person->updaterID   = $this->currentPerson->personID;
            $person->save();

            $op           = new OrgPerson;
            $op->orgID    = $event->orgID;
            $op->personID = $person->personID;
            $op->save();

            $email            = new Email;
            $email->personID  = $person->personID;
            $email->emailADDR = $checkEmail;
            $email->isPrimary = 1;
            $email->save();

            $regBy  = $this->currentPerson->firstName . " " . $this->currentPerson->lastName;
            $regMem = 'Non-Member';
        }

        $reg                   = new Registration;
        $reg->eventID          = $event->eventID;
        $reg->ticketID         = request()->input('ticketID');
        $reg->personID         = $person->personID;
        $reg->reportedIndustry = $indName;
        $reg->eventTopics      = $eventTopics;
        $reg->isFirstEvent     = request()->input('isFirstEvent') !== null ? 1 : 0;
        $reg->isAuthPDU        = request()->input('isAuthPDU') !== null ? 1 : 0;
        $reg->eventQuestion    = $eventQuestion;
        $reg->canNetwork       = request()->input('canNetwork') !== null ? 1 : 0;
        $reg->affiliation      = implode(",", $affiliation);
        $reg->regStatus        = 'In Progress';
        $reg->registeredBy     = $regBy;
        $reg->token            = request()->input('_token');
        $reg->subtotal         = $subtotal;
        $reg->origcost         = $origcost;
        $reg->membership       = $regMem;
        if($event->hasFood) {
            $reg->specialNeeds = $specialNeeds;
            $reg->allergenInfo = implode(",", $allergenInfo);
            $reg->cityState    = $cityState;
            $reg->eventNotes   = $eventNotes;
        }
        $reg->save();

        // ----------------------------------------------------------

        for($i = 2; $i <= $quantity; $i++) {

            $prefix        = request()->input('prefix' . "_$i");
            $firstName     = request()->input('firstName' . "_$i");
            $middleName    = request()->input('middleName' . "_$i");
            $lastName      = request()->input('lastName' . "_$i");
            $suffix        = request()->input('suffix' . "_$i");
            $prefName      = request()->input('prefName' . "_$i");
            $compName      = request()->input('compName' . "_$i");
            $indName       = request()->input('indName' . "_$i");
            $title         = request()->input('title' . "_$i");
            $eventQuestion = request()->input('eventQuestion' . "_$i");
            $eventTopics   = request()->input('eventTopics' . "_$i");
            $checkEmail    = request()->input('login' . "_$i");
            $subtotal      = request()->input('sub' . $i);
            $origcost      = request()->input('cost' . $i);

            if($event->hasFood) {
                $specialNeeds = request()->input('specialNeeds' . "_$i");
                $eventNotes   = request()->input('eventNotes' . "_$i");
                $allergenInfo = implode(",", request()->input('allergenInfo' . "_$i"));
                $cityState    = request()->input('cityState' . "_$i");
            }

            $subcheck += $subtotal;

            if(!Auth::check() && $email === null) {
                // Not logged in and email is not in database; must create
                $person               = new Person;
                $person->prefix       = $prefix;
                $person->firstName    = $firstName;
                $person->midName      = $middleName;
                $person->lastName     = $lastName;
                $person->suffix       = $suffix;
                $person->defaultOrgID = $event->orgID;
                $person->prefName     = $prefName;
                $person->compName     = $compName;
                $person->indName      = $indName;
                $person->title        = $title;
                if($event->hasFood) {
                    $person->allergenInfo = implode(",", $allergenInfo);
                }
                $person->affiliation = implode(",", $affiliation);
                $person->save();

                $op           = new OrgPerson;
                $op->orgID    = $event->orgID;
                $op->personID = $person->personID;
                $op->save();

                $email            = new Email;
                $email->personID  = $person->personID;
                $email->emailADDR = $checkEmail;
                $email->isPrimary = 1;
                $email->save();

                $regBy  = $person->firstName . " " . $person->lastName;
                $regMem = 'Non-Member';

            } elseif(Auth::check() && ($email->personID == $this->currentPerson->personID)) {
                // the email entered belongs to the person logged in; ergo in DB
                // addresses #2 - whatever should NOT be the same as the first
                $person               = $this->currentPerson;
                $person->prefix       = $prefix;
                $person->firstName    = $firstName;   // this doesn't get edited
                $person->midName      = $middleName;
                $person->lastName     = $lastName;    // this doesn't get edited
                $person->suffix       = $suffix;
                $person->defaultOrgID = $event->orgID;
                $person->prefName     = $prefName;
                $person->compName     = $compName;
                $person->indName      = $indName;
                $person->title        = $title;
                if($event->hasFood) {
                    $person->allergenInfo = implode(",", $allergenInfo);
                }
                $person->affiliation = implode(",", $affiliation);
                $person->save();

                $regBy = $person->firstName . " " . $person->lastName;
                if($person->orgperson->OrgStat1 === null) {
                    $regMem = 'Non-Member';
                } else {
                    $regMem = 'Member';
                }

            } elseif(Auth::check() && ($email->personID != $this->currentPerson->personID)) {
                // someone logged in is registering someone else in the DB (usually CAMI)
                $person               = Person::find($email->personID);
                $person->prefix       = $prefix;
                $person->firstName    = $firstName;   // this doesn't get edited
                $person->midName      = $middleName;
                $person->lastName     = $lastName;    // this doesn't get edited
                $person->suffix       = $suffix;
                $person->defaultOrgID = $event->orgID;
                $person->prefName     = $prefName;
                $person->compName     = $compName;
                $person->indName      = $indName;
                $person->title        = $title;
                if($event->hasFood) {
                    $person->allergenInfo = implode(",", $allergenInfo);
                }
                $person->affiliation = implode(",", $affiliation);
                $person->save();

                $regBy = $this->currentPerson->firstName . " " . $this->currentPerson->lastName;
                if($person->orgperson->OrgStat1 === null) {
                    $regMem = 'Non-Member';
                } else {
                    $regMem = 'Member';
                }

            } else {
                // someone logged in is registering for someone else NOT in the DB
                $person               = new Person;
                $person->prefix       = $prefix;
                $person->firstName    = $firstName;
                $person->midName      = $middleName;
                $person->lastName     = $lastName;
                $person->suffix       = $suffix;
                $person->defaultOrgID = $event->orgID;
                $person->prefName     = $prefName;
                $person->compName     = $compName;
                $person->indName      = $indName;
                $person->title        = $title;
                if($event->hasFood) {
                    $person->allergenInfo = implode(",", $allergenInfo);
                }
                $person->affiliation = implode(",", $affiliation);
                $person->creatorID   = $this->currentPerson->personID;
                $person->updaterID   = $this->currentPerson->personID;
                $person->save();

                $op           = new OrgPerson;
                $op->orgID    = $event->orgID;
                $op->personID = $person->personID;
                $op->save();

                $email            = new Email;
                $email->personID  = $person->personID;
                $email->emailADDR = $checkEmail;
                $email->isPrimary = 1;
                $email->save();

                $regBy  = $this->currentPerson->firstName . " " . $this->currentPerson->lastName;
                $regMem = 'Non-Member';
            }

            $reg                   = new Registration;
            $reg->eventID          = $event->eventID;
            $reg->ticketID         = request()->input('ticketID');
            $reg->personID         = $person->personID;
            $reg->reportedIndustry = $indName;
            $reg->eventTopics      = $eventTopics;
            $reg->isFirstEvent     = request()->input('isFirstEvent') !== null ? 1 : 0;
            $reg->isAuthPDU        = request()->input('isAuthPDU') !== null ? 1 : 0;
            $reg->eventQuestion    = $eventQuestion;
            $reg->canNetwork       = request()->input('canNetwork') !== null ? 1 : 0;
            $reg->affiliation      = implode(",", $affiliation);
            $reg->regStatus        = 'In Progress';
            $reg->registeredBy     = $regBy;
            $reg->token            = request()->input('_token');
            $reg->subtotal         = $subtotal;
            $reg->origcost         = $origcost;
            $reg->membership       = $regMem;
            if($event->hasFood) {
                $reg->cityState    = $cityState;
                $reg->allergenInfo = implode(",", $allergenInfo);
                $reg->specialNeeds = $specialNeeds;
                $reg->eventNotes   = $eventNotes;
            }
            $reg->save();
        }

        // ----------------------------------------------------------
        if($subtotal != $total) {
            return Redirect::back()->withErrors(['msg', "Something funky happened with the math.  Don't hack the form!"]);
        } else {
            $rf               = new RegFinance;
            $rf->regID        = $reg->regID;
            $rf->creatorID    = $this->currentPerson->personID;
            $rf->updaterID    = $this->currentPerson->personID;
            $rf->personID     = $this->currentPerson->personID;
            $rf->ticketID     = $ticketID;
            $rf->eventID      = $event->eventID;
            $rf->seats        = $quantity;
            $rf->cost         = $total;
            $rf->discountCode = $dCode;
            $rf->token        = request()->input('_token');
            if($flatamt > 0) {
                $rf->discountAmt = $flatamt;
            } else {
                $rf->discountAmt = $total - $subcheck;
            }
            Auth::check() ? $rf->creatorID = auth()->user()->id : $rf->creatorID = 1;
            Auth::check() ? $rf->updaterID = auth()->user()->id : $rf->creatorID = 1;
            $rf->save();

            // Everything is saved and updated and such, now display the data back for review
            return redirect('/confirm_registration/' . $reg->regID);
        }
    }

    public function edit ($id) {
        // responds to GET /blah/id/edit and shows the add/edit form
    }

    public function update (Request $request, $id) {
        // responds to PATCH /blah/id
    }

    public function destroy ($id) {
        // responds to DELETE /blah/id
    }
}

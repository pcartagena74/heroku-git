<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 0);

use App\Address;
use App\OrgPerson;
use App\Person;
use App\PersonStaging;
use App\Event;
use App\Email;
use App\RegFinance;
use App\Registration;
use App\RegSession;
use App\Ticket;
use App\User;
use App\Phone;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class UploadController extends Controller
{
    public function __construct () {
        $this->middleware('auth');
    }

    public function index () {
        // displays the data upload form

        $user = Person::find(auth()->user()->id);

        $events = Event::where([
            ['orgID', '=', $user->defaultOrgID]
        ])->withCount('registrations')->get();

        return view('v1.auth_pages.organization.data_upload', compact('events'));
    }

    public function show ($id) {
        // responds to GET /blah/id
    }

    public function create () {
        // responds to /blah/create and shows add/edit form
    }

    public function store (Request $request) {
        // responds to POST to /blah and creates, adds, stores the event

        $this->counter = 0;
        $this->currentPerson = Person::find(auth()->user()->id);
        $what                = request()->input('data_type');
        $filename            = $_FILES['filename']['tmp_name'];
        $eventID = request()->input('eventID');

        if($what == 'evtdata' && ($eventID === null || $eventID == 'Select an event...')){
            // go back with message
            return Redirect::back()->with('alert-warning', 'You must select an event.');
        }

        switch ($what) {
            case 'mbrdata':
//                try {
                    DB::transaction(function () {
                        $filename            = $_FILES['filename']['tmp_name'];
                        Excel::load($filename, function($reader) {
                            $results = $reader->get();
                            $orgID = $this->currentPerson->defaultOrgID;
                            foreach($results as $row) {
                                $this->counter++;
                                // columns in this sheet are fixed; check directly and then add if not found...
                                // foreach $row, search on $row->pmi_id, then $row->primary_email, then $row->alternate_email
                                // if found, get $person, $org-person, $email, $address, $phone records and update, else create

                                $op     = OrgPerson::where('OrgStat1', '=', (integer)$row->pmi_id)->first();
                                $em1  = trim(strtolower($row->primary_email));
                                $em2  = trim(strtolower($row->alternate_email));
                                $emchk1 = Email::where('emailADDR', '=', $em1)->first();
                                if($emchk1 !== null) {
                                    $emchk1 = $emchk1->emailADDR;
                                }
                                $emchk2 = Email::where('emailADDR', '=', $em2)->first();
                                if($emchk2 !== null) {
                                    $emchk2 = $emchk2->emailADDR;
                                }
                                $pchk = Person::where([
                                    ['firstName', '=', $row->first_name],
                                    ['lastName', '=', $row->last_name]
                                ])->first();

                                if($op === null && $emchk1 === null && $emchk2 === null && $pchk === null) {
                                    // PMI ID, emails, first/last name are not found so person is completely new; create all records

                                    $p               = new Person;
                                    $u               = new User;
                                    $p->prefix       = trim(ucwords($row->prefix));
                                    $p->firstName    = trim(ucwords($row->first_name));
                                    $p->midName      = trim(ucwords($row->middle_name));
                                    $p->lastName     = trim(ucwords($row->last_name));
                                    $p->suffix       = trim(ucwords($row->suffix));
                                    $p->title        = trim(ucwords($row->title));
                                    $p->compName     = trim(ucwords($row->company));
                                    $p->creatorID    = auth()->user()->id;
                                    $p->defaultOrgID = $this->currentPerson->defaultOrgID;

                                    if($em1 !== null && $em1 != "" && $em1 != " " && $em1 != $emchk1 && $em1 != $emchk2) {
                                        $p->login = $em1;
                                        $p->save();
                                        $u->id    = $p->personID;
                                        $u->login = $em1;
                                        $u->email = $em1;
                                        $u->save();

                                        $e            = new Email;
                                        $e->personID  = $p->personID;
                                        $e->emailADDR = $em1;
                                        $e->isPrimary = 1;
                                        $e->creatorID = auth()->user()->id;
                                        $e->updaterID = auth()->user()->id;
                                        $e->save();

                                    } elseif($em2 !== null && $em2 != '' && $em2 != ' ' && $em2 != $emchk2 && $em2 != $emchk1) {
                                        $p->login = $em2;
                                        $p->save();
                                        $u->id    = $p->personID;
                                        $u->login = $em2;
                                        $u->email = $em2;
                                        $u->save();

                                        $e            = new Email;
                                        $e->personID  = $p->personID;
                                        $e->emailADDR = $em2;
                                        $e->isPrimary = 1;
                                        $e->creatorID = auth()->user()->id;
                                        $e->updaterID = auth()->user()->id;
                                        $e->save();
                                    } else {
                                        // This is a last resort when there are no email addresses associated with the record
                                        // Better to abandon; avoid $p->save();
                                        next;
                                    }

                                    $newOP            = new OrgPerson;
                                    $newOP->orgID     = $p->defaultOrgID;
                                    $newOP->personID  = $p->personID;
                                    $newOP->OrgStat1  = (integer)$row->pmi_id;
                                    $newOP->OrgStat2  = trim(ucwords($row->chapter_member_class));
                                    $newOP->RelDate1  =
                                        Carbon::createFromFormat('d/m/Y', $row->pmi_join_date)->toDateTimeString();
                                    $newOP->RelDate2  =
                                        Carbon::createFromFormat('d/m/Y', $row->chapter_join_date)->toDateTimeString();
                                    $newOP->RelDate3  =
                                        Carbon::createFromFormat('d/m/Y', $row->pmi_expiration)->toDateTimeString();
                                    $newOP->RelDate4  =
                                        Carbon::createFromFormat('d/m/Y', $row->chapter_expiration)->toDateTimeString();
                                    $newOP->creatorID = auth()->user()->id;
                                    $newOP->save();


                                    if($row->preferred_address !== null && $row->preferred_address != "" && $row->preferred_address != " ") {
                                        $addr           = new Address;
                                        $addr->personID = $p->personID;
                                        $addr->addrTYPE = trim(ucwords($row->preferred_address_type));
                                        $addr->addr1    = trim(ucwords($row->preferred_address));
                                        $addr->city     = trim(ucwords($row->city));
                                        $addr->state    = trim(ucwords($row->state));
                                        $z              = (integer)trim(ucwords($row->zip));
                                        if(strlen($z) == 4) {
                                            $z = "0" . $z;
                                        } elseif(strlen($z) == 8) {
                                            $r2 = substr($z, -4);
                                            $l2 = substr($z, 4);
                                            $z  = "0" . $l2 . "-" . $r2;
                                        }
                                        $addr->zip = $z;

                                        // Need a smarter way to determine country code
                                        if(trim(ucwords($row->country)) == 'United States') {
                                            $addr->cntryID = 228;
                                        } elseif(trim(ucwords($row->country)) == 'Canada') {
                                            $addr->cntryID = 36;
                                        }
                                        $addr->creatorID = auth()->user()->id;
                                        $addr->updaterID = auth()->user()->id;
                                        $addr->save();
                                    }

                                    if($em1 !== null && $em2 !== null && $em2 != $em1 && $em2 != "" && $em2 != " ") {
                                        $e            = new Email;
                                        $e->personID  = $p->personID;
                                        $e->emailADDR = $em2;
                                        $e->creatorID = auth()->user()->id;
                                        $e->updaterID = auth()->user()->id;
                                        $e->save();
                                    }

                                    if($row->home_phone !== null) {
                                        $fone              = new Phone;
                                        $fone->personID    = $p->personID;
                                        $fone->phoneNumber = (integer)$row->home_phone;
                                        $fone->phoneType   = 'Home';
                                        $fone->creatorID   = auth()->user()->id;
                                        $fone->updaterID   = auth()->user()->id;
                                        $fone->save();
                                    }

                                    if($row->work_phone !== null && $row->work_phone != $row->home_phone) {
                                        $fone              = new Phone;
                                        $fone->personID    = $p->personID;
                                        $fone->phoneNumber = (integer)$row->work_phone;
                                        $fone->phoneType   = 'Work';
                                        $fone->creatorID   = auth()->user()->id;
                                        $fone->updaterID   = auth()->user()->id;
                                        $fone->save();
                                    }

                                    if($row->mobile_phone !== null && $row->mobile_phone != $row->work_phone && $row->mobile_phone != $row->home_phone) {
                                        $fone              = new Phone;
                                        $fone->personID    = $p->personID;
                                        $fone->phoneNumber = (integer)$row->mobile_phone;
                                        $fone->phoneType   = 'Mobile';
                                        $fone->creatorID   = auth()->user()->id;
                                        $fone->updaterID   = auth()->user()->id;
                                        $fone->save();
                                    }
                                } elseif($op !== null) {
                                    // There was an org-person record (found by $OrgStat1 == PMI ID)
                                    $newOP            = $op;
                                    $newOP->OrgStat2  = trim(ucwords($row->chapter_member_class));
                                    if(isset($row->pmi_join_date)){
                                        $newOP->RelDate1  =
                                            Carbon::createFromFormat('d/m/Y', $row->pmi_join_date)->toDateTimeString();
                                    }
                                    if(isset($row->chapter_join_date)){
                                        $newOP->RelDate2  =
                                            Carbon::createFromFormat('d/m/Y', $row->chapter_join_date)->toDateTimeString();
                                    }
                                    if(isset($row->pmi_expiration)){
                                        $newOP->RelDate3  =
                                            Carbon::createFromFormat('d/m/Y', $row->pmi_expiration)->toDateTimeString();
                                    }
                                    if(isset($row->pmi_expiration)){
                                        $newOP->RelDate4  =
                                            Carbon::createFromFormat('d/m/Y', $row->chapter_expiration)->toDateTimeString();
                                    }
                                    $newOP->updaterID = auth()->user()->id;
                                    $newOP->save();

                                    $p = Person::find($newOP->personID);
                                    if($em1 !== null && $em1 != "" && $em1 != " " && $em1 != $emchk1) {
                                        $e            = new Email;
                                        $e->personID  = $p->personID;
                                        $e->emailADDR = $em1;
                                        $e->isPrimary = 1;
                                        $e->creatorID = auth()->user()->id;
                                        $e->updaterID = auth()->user()->id;
                                        $e->save();

                                    }
                                    if($em2 !== null && $em2 != "" && $em2 != " " && $em2 != $emchk2 && $em2 != $em1) {
                                        $e            = new Email;
                                        $e->personID  = $p->personID;
                                        $e->emailADDR = $em2;
                                        $e->isPrimary = 1;
                                        $e->creatorID = auth()->user()->id;
                                        $e->updaterID = auth()->user()->id;
                                        $e->save();
                                    }

                                    $fone = Phone::where([
                                        ['phoneNumber', '=', $row->home_phone],
                                        ['phoneType', '=', 'Home']
                                    ])->first();
                                    if($row->home_phone !== null && $fone === null) {
                                        $fone              = new Phone;
                                        $fone->personID    = $p->personID;
                                        $fone->phoneNumber = (integer)$row->home_phone;
                                        $fone->phoneType   = 'Home';
                                        $fone->creatorID   = auth()->user()->id;
                                        $fone->updaterID   = auth()->user()->id;
                                        $fone->save();
                                    }

                                    $fone = Phone::where([
                                        ['phoneNumber', '=', $row->work_phone],
                                        ['phoneType', '=', 'Work']
                                    ])->first();
                                    if($row->work_phone !== null && $fone === null && $row->work_phone != $row->home_phone) {
                                        $fone              = new Phone;
                                        $fone->personID    = $p->personID;
                                        $fone->phoneNumber = (integer)$row->work_phone;
                                        $fone->phoneType   = 'Work';
                                        $fone->creatorID   = auth()->user()->id;
                                        $fone->updaterID   = auth()->user()->id;
                                        $fone->save();
                                    }

                                    $fone = Phone::where([
                                        ['phoneNumber', '=', $row->mobile_phone],
                                        ['phoneType', '=', 'Mobile']
                                    ])->first();
                                    if($row->mobile_phone !== null && $fone === null &&
                                        $row->mobile_phone != $row->work_phone && $row->mobile_phone != $row->home_phone
                                    ) {
                                        $fone              = new Phone;
                                        $fone->personID    = $p->personID;
                                        $fone->phoneNumber = (integer)$row->mobile_phone;
                                        $fone->phoneType   = 'Mobile';
                                        $fone->creatorID   = auth()->user()->id;
                                        $fone->updaterID   = auth()->user()->id;
                                        $fone->save();
                                    }

                                    $addr = Address::where('addr1', '=', trim(ucwords($row->preferred_address)))->get();
                                    if($addr === null && $row->preferred_address !== null && $row->preferred_address != "" && $row->preferred_address != " ") {
                                        $addr           = new Address;
                                        $addr->personID = $p->personID;
                                        $addr->addrTYPE = trim(ucwords($row->preferred_address_type));
                                        $addr->addr1    = trim(ucwords($row->preferred_address));
                                        $addr->city     = trim(ucwords($row->city));
                                        $addr->state    = trim(ucwords($row->state));
                                        $z              = (integer)trim(ucwords($row->zip));
                                        if(strlen($z) == 4) {
                                            $z = "0" . $z;
                                        } elseif(strlen($z) == 8) {
                                            $r2 = substr($z, -4);
                                            $l2 = substr($z, 4);
                                            $z  = "0" . $l2 . "-" . $r2;
                                        }
                                        $addr->zip = $z;
                                        if(trim(ucwords($row->country)) == 'United States') {
                                            $addr->cntryID = 228;
                                        } elseif(trim(ucwords($row->country)) == 'Canada') {
                                            $addr->cntryID = 36;
                                        }
                                        $addr->creatorID = auth()->user()->id;
                                        $addr->updaterID = auth()->user()->id;
                                        $addr->save();

                                        $p               = new PersonStaging;
                                        $p->prefix       = trim(ucwords($row->prefix));
                                        $p->firstName    = trim(ucwords($row->first_name));
                                        $p->midName      = trim(ucwords($row->middle_name));
                                        $p->lastName     = trim(ucwords($row->last_name));
                                        $p->suffix       = trim(ucwords($row->suffix));
                                        $p->title        = trim(ucwords($row->title));
                                        $p->compName     = trim(ucwords($row->company));
                                        $p->defaultOrgID = $this->currentPerson->defaultOrgID;
                                        $p->creatorID    = auth()->user()->id;
                                        $p->save();
                                    }
                                } elseif($emchk1 !== null && $em1 !== null && $em1 != '' && $em1 != ' ') {

                                } elseif($emchk2 !== null && $em2 !== null && $em2 != '' && $em2 != ' ') {

                                }

                            }
                        }); // end of Excel chunk
                   }); // end of database transaction
/*
                } catch(Exception $exception) {
                    Session::flash('alert-danger', 'The member data did not load properly.');
                    Session::flash('alert-warning', $exception->getMessage());
                    echo($exception->getMessage());
                    $events = Event::where([
                        ['orgID', '=', $this->currentPerson->defaultOrgID]
                    ])->get();
                    return view('v1.auth_pages.organization.data_upload', compact('events', 'exception'));
                }
*/
                break;

            case
            'evtdata':
//                try {
//                    DB::transaction(function () {
                        $filename            = $_FILES['filename']['tmp_name'];
                        Excel::load($filename, function($reader) {
                            $results = $reader->get();

                            $eventID = request()->input('eventID');
                            $tktID   = Ticket::where('eventID', '=', $eventID)->first();
                            if($tktID === null) {
                                $e                      = Event::find($eventID);
                                $t                      = new Ticket;
                                $t->eventID             = $eventID;
                                $t->ticketLabel         = 'Default Ticket';
                                $t->availabilityEndDate = $e->eventStartDate;
                                $t->creatorID           = $this->currentPerson->personID;
                                $t->save();
                                $tktID = $t->ticketID;
                            } else {
                                $tktID = $tktID->ticketID;
                            }
                            $orgID         = $this->currentPerson->defaultOrgID;
                            $rows          = $results->toArray();

                            foreach($rows as $row) {
                                $this->counter++;
                                $create_user = 0;
                                foreach(array_keys($row) as $k) {
                                    //dd($row);
                                    switch (1) {
                                        case preg_match('/attended/i', $k):
                                            break;
                                        case preg_match('/^ticket$/i', $k):
                                            $tktTxt = $row[$k];
                                            if(preg_match('/Bundle/i', $tktTxt)){
                                                $ticketID = 126;
                                            } elseif(preg_match('/Friday Only/i', $tktTxt)){
                                                $ticketID = 123;
                                            } elseif(preg_match('/Saturday Only/i', $tktTxt)){
                                                $ticketID = 124;
                                            } elseif(preg_match('/Friday evening/i', $tktTxt)){
                                                $ticketID = 125;
                                            }
                                            break;
                                        case preg_match('/salutation/i', $k):
                                            $prefix = trim(substr($row[$k], 0, 5));
                                            break;
                                        case (preg_match('/^first.name$/i', $k)):
                                            $first = ucwords($row[$k]);
                                            break;
                                        case (preg_match('/last.name/i', $k)):
                                            $last = ucwords($row[$k]);
                                            break;
                                        case (preg_match('/suffix/i', $k)):
                                            $suffix = trim(substr($row[$k], 0, 9));
                                            break;
                                        case (preg_match('/email/i', $k)):
                                            $email = $row[$k];
                                            break;
                                        case (preg_match('/^ticket$/i', $k)):
                                            $tkt = $row[$k];
                                            break;
                                        case (preg_match('/seat/i', $k)):
                                            $seat = $row[$k];
                                            break;
                                        case (preg_match('/registered.on/i', $k)):
                                            $regDate = $row[$k];
                                            break;
                                        case (preg_match('/registered.by/i', $k)):
                                            $regBy = $row[$k];
                                            break;
                                        case (preg_match('/^payment$/i', $k)):
                                            $pmtType = $row[$k];
                                            break;
                                        case (preg_match('/confirmation.co/i', $k)):
                                            $confCode = $row[$k];
                                            break;
                                        case (preg_match('/payment.rec/i', $k)):
                                            $pmtRecd = $row[$k];
                                            if(preg_match('/[no|0]/i', $pmtRecd)) {
                                                $pmtRecd = 0;
                                            } else {
                                                $pmtRecd = 1;
                                            }
                                            break;
                                        case (preg_match('/^status$/i', $k)):
                                            $status = $row[$k];
                                            break;
                                        case (preg_match('/cancelled.on/i', $k)):
                                            $cancelDate = $row[$k];
                                            break;
                                        case (preg_match('/^cost$/i', $k)):
                                            $cost = $row[$k];
                                            break;
                                        case (preg_match('/cc.charge/i', $k)):
                                            $ccFee = $row[$k];
                                            break;
                                        case (preg_match('/handling/i', $k)):
                                            $handleFee = $row[$k];
                                            break;
                                        case (preg_match('/your.amount/i', $k)):
                                            $orgAmt = $row[$k];
                                            break;
                                        case (preg_match('/disc.code/i', $k)):
                                            $disCode = $row[$k];
                                            break;
                                        case (preg_match('/disc.desc/i', $k)):
                                            $discDesc = $row[$k];
                                            break;
                                        case (preg_match('/disc.amount/i', $k)):
                                            $discAmt = $row[$k];
                                            break;
                                        case (preg_match('/pmi.(number|membership)/i', $k)):
                                            $pmiID = $row[$k];
                                            if(!is_numeric($pmiID)) {
                                                $pmiID = null;
                                            } else {
                                                $pmiID = number_format($pmiID, 0, '', '');
                                            }
                                            break;
                                        case (preg_match('/preferred.first/i', $k)):
                                            $prefName = $row[$k];
                                            break;
                                        case (preg_match('/topic/i', $k)):
                                            $topics = $row[$k];
                                            break;
                                        case (preg_match('/(pmp|certification)/i', $k)):
                                            $hasPMP = $row[$k];
                                            if(preg_match('/Yes/i', $hasPMP)) {
                                                $hasPMP = 1;
                                            } else {
                                                $hasPMP = 0;
                                            }
                                            break;
                                        case (preg_match('/first.time/i', $k)):
                                            $firstEvent = $row[$k];
                                            if($firstEvent !== null) {
                                                $firstEvent = 1;
                                            } else {
                                                $firstEvent = 0;
                                            }
                                            if(preg_match('/[no|0]/i', $firstEvent)) {
                                                $firstEvent = 0;
                                            }
                                            break;
                                        case (preg_match('/city.and.state/i', $k)):
                                            $commute = $row[$k];
                                            break;
                                        case (preg_match('/company.name/i', $k)):
                                            $coName = $row[$k];
                                            break;
                                        case (preg_match('/authorize/i', $k)):
                                            $canPDU = $row[$k];
                                            if($canPDU === null) {
                                                $canPDU = 0;
                                            } else {
                                                $canPDU = 1;
                                            }
                                            if(preg_match('/[no|0]/i', $canPDU)) {
                                                $canPDU = 0;
                                            }
                                            break;
                                        case (preg_match('/[question(s)*|challenges]/i', $k)):
                                            $questions = $row[$k];
                                            break;
                                        case (preg_match('/(allergy)|(dietary)/i', $k)):
                                            $allergy = $row[$k];
                                            break;
                                        case (preg_match('/industry/i', $k)):
                                            $indName = $row[$k];
                                            break;
                                        case (preg_grep('/[(networking)|(registration.list)|(roster)]/i', $k)):
                                            $canNtwk = $row[$k];
                                            if($canNtwk === null) {
                                                $canNtwk = 0;
                                            } else {
                                                $canNtwk = 1;
                                            }
                                            if(preg_match('/[no|0]/i', $canNtwk)) {
                                                $canNtwk = 0;
                                            }
                                            break;
                                        case (preg_match('/special|wheel/i', $k)):
                                            $specialNeeds = $row[$k];
                                            break;
                                        case (preg_match('/hear/i', $k)):
                                            $hear = $row[$k];
                                            break;
                                        case (preg_match('/affiliation/i', $k)):
                                            $affiliation = $row[$k];
                                            break;
                                        case (preg_match('/experience/i', $k)):
                                            $experience = $row[$k];
                                            break;
                                        case (preg_match('/1_Friday$/i', $k)):
                                            $f1 = $row[$k];
                                            //dd($f1);
                                            break;
                                        case (preg_match('/1_Saturday$/i', $k)):
                                            $s1 = $row[$k];
                                            break;
                                        case (preg_match('/2_Friday$/i', $k)):
                                            $f2 = $row[$k];
                                            break;
                                        case (preg_match('/2_Saturday$/i', $k)):
                                            $s2 = $row[$k];
                                            break;
                                        case (preg_match('/3_Friday$/i', $k)):
                                            $f3 = $row[$k];
                                            break;
                                        case (preg_match('/3_Saturday$/i', $k)):
                                            $s3 = $row[$k];
                                            break;

                                        default:
                                            echo("Encountered an unknown column: '" . $k . "'<br>");
                                            break;
                                    }
                                }
                                //dd(get_defined_vars());
                                if($eventID == 97 && ($ticketID == 123 || $ticketID == 126)) {
                                    if(preg_match('/^AGILE/i', $f1)) {
                                        $fs1 = 91;
                                    } elseif(preg_match('/^PORTFOLIO/i', $f1)) {
                                        $fs1 = 92;
                                    } elseif(preg_match('/^RISK/i', $f1)) {
                                        $fs1 = 93;
                                    } else {
                                        $fs1 = null;
                                    }
                                    if(preg_match('/^AGILE/i', $f2)) {
                                        $fs2 = 96;
                                    } elseif(preg_match('/^PORTFOLIO/i', $f2)) {
                                        $fs2 = 97;
                                    } elseif(preg_match('/^RISK/i', $f2)) {
                                        $fs2 = 98;
                                    } else {
                                        $fs2 = null;
                                    }
                                    if(preg_match('/^AGILE/i', $f3)) {
                                        $fs3 = 101;
                                    } elseif(preg_match('/^PORTFOLIO/i', $f3)) {
                                        $fs3 = 102;
                                    } elseif(preg_match('/^RISK/i', $f3)) {
                                        $fs3 = 103;
                                    } else {
                                        $fs3 = null;
                                    }
                                }
                                if($eventID == 97 && ($ticketID == 124 || $ticketID == 126)){
                                    if (preg_match('/^AGILE/i', $s1)) {
                                        $ss1 = 106;
                                    } elseif (preg_match('/^PORTFOLIO/i', $s1)) {
                                        $ss1 = 107;
                                    } elseif (preg_match('/^RISK/i', $s1)) {
                                        $ss1 = 108;
                                    } else {
                                        $ss1 = null;
                                    }
                                    if (preg_match('/^AGILE/i', $s2)) {
                                        $ss2 = 111;
                                    } elseif (preg_match('/^PORTFOLIO/i', $s2)) {
                                        $ss2 = 112;
                                    } elseif (preg_match('/^RISK/i', $s2)) {
                                        $ss2 = 113;
                                    } else {
                                        $ss2 = null;
                                    }
                                    if (preg_match('/^AGILE/i', $s3)) {
                                        $ss3 = 116;
                                    } elseif (preg_match('/^PORTFOLIO/i', $s3)) {
                                        $ss3 = 117;
                                    } elseif (preg_match('/^RISK/i', $s3)) {
                                        $ss3 = 118;
                                    } else {
                                        $ss3 = null;
                                    }
                                }
                                if(!isset($canNtwk)) {
                                    $canNtwk = 0;
                                }
                                if(!isset($allergy)) {
                                    $allergy = 0;
                                }
                                if(!isset($pmiID)) {
                                    $pmiID = null;
                                }
                                if(!isset($canPDU)) {
                                    $canPDU = 0;
                                }
                                if(!isset($firstEvent)) {
                                    $firstEvent = 0;
                                }
                                if(!isset($firstEvent)) {
                                    $firstEvent = 0;
                                }
                                if(!isset($pmtRecd)) {
                                    $pmtRecd = 0;
                                }
                                if(!isset($specialNeeds)) {
                                    $specialNeeds = null;
                                }
                                if(!isset($coName)) {
                                    $coName = null;
                                }
                                if(!isset($indName)) {
                                    $indName = null;
                                }
                                if(!isset($hear)) {
                                    $hear = null;
                                }
                                if(!isset($questions)) {
                                    $questions = null;
                                }
                                if(!isset($commute)) {
                                    $commute = null;
                                }
                                if(!isset($topics)) {
                                    $topics = null;
                                }
                                if(!isset($prefName)) {
                                    $prefName = null;
                                }
                                // foreach cycle through $row's keys() and switch on preg_match
                                // do the following things to input data:
                                // 1. set the following items to lower case: email addresses
                                // 2. perform date formatting to get into database if needed

                                // Then perform these steps:
                                // 1. Check if $row->email or $row->pmiID is in person-email table
                                // 2. If found, get $person and $orgperson record and:
                                //    a. validate or change defaultOrgID on person
                                //    b. validate or add an org-person record for the current orgID
                                //    c. do NOT change first or last name if PMI ID exists but if first names don't match consider preferred name
                                //    d. Add to existing $person record any non-null values
                                // 4. If not found, create new $person, $org-person and $email records
                                // 5. Create new event-registration and regFinance records and give regFinance record the same regID

                                //dd(get_defined_vars());
                                if($pmiID !== null) {
                                    // record of PMI member
                                    $op = OrgPerson::where('OrgStat1', '=', $pmiID)->first();
                                    if($op !== null) {
                                        // record of PMI member found in DB and matches provided PMI ID
                                        $create_user = 0;
                                        $p               = Person::find($op->personID);
                                        $p->defaultOrgID = $orgID;
                                        $p->updaterID    = $this->currentPerson->personID;
                                        if($experience != null && $p->experience === null){
                                            $p->experience = $experience;
                                        }
                                        $p->save();

                                        $e = Email::where('emailADDR', '=', $email)->first();
                                        if($e === null) {
                                            $e            = new Email;
                                            $e->personID  = $p->personID;
                                            $e->emailADDR = $email;
                                            $e->creatorID = $this->currentPerson->personID;
                                            $e->save();
                                        }
                                    } elseif($email !== null) {
                                        $e = Email::where('emailADDR', '=', $email)->first();
                                        if($e !== null) {
                                            // record of registrant found, by $email, in DB but no $pmiID provided with reg
                                            $create_user = 0;
                                            $p               = Person::find($e->personID);
                                            $p->defaultOrgID = $orgID;
                                            $p->updaterID    = $this->currentPerson->personID;
                                            if($experience != null && $p->experience === null){
                                                $p->experience = $experience;
                                            }
                                            $p->save();

                                            if($p->firstName != $first || $p->lastName != $last || $p->compName !== $coName) {
                                                // creating a staging record instead of merging person records just in case
                                                $ps               = new PersonStaging;
                                                $ps->personID     = $p->personID;
                                                $ps->prefix       = $prefix;
                                                $ps->firstName    = $first;
                                                $ps->lastName     = $last;
                                                $ps->suffix       = $suffix;
                                                $ps->defaultOrgID = $orgID;
                                                $ps->prefName     = $prefName;
                                                $ps->login        = $email;
                                                $ps->compName     = $coName;
                                                $ps->indName      = $indName;
                                                $ps->experience   = $experience;
                                                if(isset($affiliation)){
                                                    $ps->affiliation  = $affiliation;
                                                } else {
                                                    $ps->affiliation  = 'PMI MassBay';
                                                }
                                                $ps->allergenInfo = $allergy;
                                                $ps->creatorID    = $this->currentPerson->personID;
                                                $ps->save();
                                            }
                                            $op = OrgPerson::where([
                                                ['personID', '=', $p->personID],
                                                ['orgID', '=', $orgID]
                                            ])->first();
                                            // If this Person didn't have an org-person record for this orgID
                                            if($op === null) {
                                                $op            = new OrgPerson;
                                                $op->personID  = $p->personID;
                                                $op->orgID     = $orgID;
                                                $op->OrgStat1  = $pmiID;
                                                $op->creatorID = $this->currentPerson->personID;
                                                $op->save();
                                            } else {
                                                // update PMI ID if one doesn't already exist
                                                if($op->OrgStat1 === null){
                                                    $op->OrgStat1 = $pmiID;
                                                    $op->updaterID = $this->currentPerson->personID;
                                                    $op->save();
                                                }
                                            }
                                        } else {
                                            // Didn't find registrant by provided PMI ID or email address
                                            $create_user = 1;
                                            $p               = new Person;
                                            $p->prefix       = $prefix;
                                            $p->firstName    = $first;
                                            $p->lastName     = $last;
                                            $p->suffix       = $suffix;
                                            $p->defaultOrgID = $orgID;
                                            $p->prefName     = $prefName;
                                            $p->login        = $email;
                                            $p->compName     = $coName;
                                            $p->indName      = $indName;
                                            if(isset($affiliation)){
                                                $p->affiliation  = $affiliation;
                                            } else {
                                                $p->affiliation  = 'PMI MassBay';
                                            }
                                            $p->allergenInfo = $allergy;
                                            $p->creatorID    = $this->currentPerson->personID;
                                            if($experience != null){
                                                $p->experience = $experience;
                                            }
                                            $p->save();

                                            $op            = new OrgPerson;
                                            $op->personID  = $p->personID;
                                            $op->orgID     = $orgID;
                                            $op->OrgStat1  = $pmiID;
                                            $op->creatorID = $this->currentPerson->personID;
                                            $op->save();

                                            $e            = new Email;
                                            $e->personID  = $p->personID;
                                            $e->emailADDR = $email;
                                            $e->creatorID = $this->currentPerson->personID;
                                            $e->save();
                                        }
                                    }
                                } else {
                                    // $pmiID wasn't provided by registrant
                                    if($email !== null) {
                                        $e = Email::where('emailADDR', '=', $email)->first();

                                        if($e !== null) {
                                            // record of this email in DB
                                            $create_user = 0;
                                            $p               = Person::find($e->personID);
                                            $p->defaultOrgID = $orgID;
                                            $p->updaterID    = $this->currentPerson->personID;
                                            $p->save();

                                            if($p->firstName != $first || $p->lastName != $last || $p->compName !== $coName) {
                                                // firstName, lastName or company name changed; making staging record
                                                $ps               = new PersonStaging;
                                                $ps->personID     = $p->personID;
                                                $ps->prefix       = $prefix;
                                                $ps->firstName    = $first;
                                                $ps->lastName     = $last;
                                                $ps->suffix       = $suffix;
                                                $ps->defaultOrgID = $orgID;
                                                $ps->prefName     = $prefName;
                                                $ps->login        = $email;
                                                $ps->compName     = $coName;
                                                $ps->indName      = $indName;
                                                if(isset($affiliation)){
                                                    $ps->affiliation  = $affiliation;
                                                } else {
                                                    $ps->affiliation  = 'PMI MassBay';
                                                }
                                                $ps->allergenInfo = $allergy;
                                                $ps->creatorID    = $this->currentPerson->personID;
                                                $ps->save();
                                            } else {
                                                $p->prefix       = $prefix;
                                                $p->suffix       = $suffix;
                                                $p->defaultOrgID = $orgID;
                                                $p->prefName     = $prefName;
                                                $p->indName      = $indName;
                                                if(isset($affiliation)){
                                                    $p->affiliation  = $affiliation;
                                                } else {
                                                    $p->affiliation  = 'PMI MassBay';
                                                }
                                                $p->allergenInfo = $allergy;
                                                $p->creatorID    = $this->currentPerson->personID;
                                                $p->save();
                                            }
                                            $op = OrgPerson::where([
                                                ['personID', '=', $p->personID],
                                                ['orgID', '=', $orgID]
                                            ])->first();
                                            if($op === null) {
                                                // need to create a stub org-person record if one doesn't already exist
                                                $op            = new OrgPerson;
                                                $op->personID  = $p->personID;
                                                $op->orgID     = $orgID;
                                                $op->creatorID = $this->currentPerson->personID;
                                                $op->save();
                                            }
                                        } else {
                                            // record of this email not in DB
                                            $create_user = 1;
                                            $p               = new Person;
                                            $p->prefix       = $prefix;
                                            $p->firstName    = $first;
                                            $p->lastName     = $last;
                                            $p->suffix       = $suffix;
                                            $p->defaultOrgID = $orgID;
                                            $p->prefName     = $prefName;
                                            $p->login        = $email;
                                            $p->compName     = $coName;
                                            $p->indName      = $indName;
                                            if(isset($affiliation)){
                                                $p->affiliation  = $affiliation;
                                            } else {
                                                $p->affiliation  = 'PMI MassBay';
                                            }
                                            $p->allergenInfo = $allergy;
                                            $p->creatorID    = $this->currentPerson->personID;
                                            $p->save();

                                            $op            = new OrgPerson;
                                            $op->personID  = $p->personID;
                                            $op->orgID     = $orgID;
                                            $op->creatorID = $this->currentPerson->personID;
                                            $op->save();

                                            $e            = new Email;
                                            $e->personID  = $p->personID;
                                            $e->emailADDR = $email;
                                            $e->creatorID = $this->currentPerson->personID;
                                            $e->save();
                                        }
                                    } else {
                                        // $pmiID was null & $email wais null
                                    }
                                }
                                if($create_user){
                                    $u = new User;
                                    $u->id = $p->personID;
                                    $u->login = $p->login;
                                    $u->email = $p->login;
                                    $u->save();
                                    $create_user = 0;
                                }
                                // do the rest of the processing for this row
                                if($eventID == 97){
                                    $tktID = $ticketID;
                                }
                                $r                   = new Registration;
                                $r->eventID          = $eventID;
                                $r->ticketID         = $tktID;
                                $r->personID         = $p->personID;
                                $r->eventTopics      = $topics;
                                $r->reportedIndustry = $indName;
                                $r->isFirstEvent     = $firstEvent;
                                $r->cityState        = $commute;
                                $r->isAuthPDU        = $canPDU;
                                $r->eventQuestion    = $questions;
                                $r->allergenInfo     = $allergy;
                                $r->canNetwork       = $canNtwk;
                                $r->specialNeeds     = $specialNeeds;
                                if(isset($affiliation)){
                                    $r->affiliation  = $affiliation;
                                } else {
                                    $r->affiliation  = 'PMI MassBay';
                                }
                                $r->regStatus        = $status;
                                $r->referalText      = $hear;
                                $r->registeredBy     = $regBy;
                                $r->discountCode     = $disCode;
                                $r->origcost         = number_format($cost, 2, '.', '');
                                $r->subtotal         = number_format($cost, 2, '.', '');
                                if(preg_match('/Non/', $tktTxt)) {
                                    $r->membership = 'Non-Member';
                                } else {
                                    $r->membership = 'Member';
                                }
                                $r->creatorID = $this->currentPerson->personID;
                                $r->save();

                                $rf               = new RegFinance;
                                $rf->regID        = $r->regID;
                                $rf->eventID      = $eventID;
                                $rf->ticketID     = $tktID;
                                $rf->personID     = $p->personID;
                                $rf->seats        = $seat;
                                $rf->cost         = number_format($cost, 2, '.', '');
                                $rf->ccFee        = number_format($ccFee, 2, '.', '');
                                $rf->handleFee    = number_format($handleFee, 2, '.', '');
                                $rf->orgAmt       = number_format($orgAmt, 2, '.', '');
                                $rf->discountCode = $disCode;
                                $rf->discountAmt  = number_format($discAmt, 2, '.', '');
                                $rf->creatorID    = $this->currentPerson->personID;
                                $rf->pmtType      = $pmtType;
                                $rf->confirmation = $confCode;
                                $rf->pmtRecd      = $pmtRecd;
                                $rf->status       = $status;
                                $rf->save();

                                if($eventID == 97){
                                    // try to decrement ticket regCounts
                                    if($ticketID == 126){
                                        $t = Ticket::find(123);
                                        $t->regCount++;
                                        $t->save();
                                        $t = Ticket::find(124);
                                        $t->regCount++;
                                        $t->save();
                                        $t = Ticket::find(125);
                                        $t->regCount++;
                                        $t->save();
                                    }else{
                                        $t = Ticket::find($ticketID);
                                        $t->regCount++;
                                        $t->save();
                                    }

                                    foreach(array($fs1, $fs2, $fs3) as $sessID) {
                                        if($sessID !== null){
                                            $rs = new RegSession;
                                            $rs->regID = $r->regID;
                                            $rs->eventID = $eventID;
                                            $rs->personID = $p->personID;
                                            $rs->sessionID = $sessID;
                                            $rs->confDay = 1;
                                            $rs->creatorID = auth()->user()->id;
                                            $rs->updaterID = auth()->user()->id;
                                            $rs->save();
                                        }
                                    }
                                    foreach(array($ss1, $ss2, $ss3) as $sessID) {
                                        if($sessID !== null){
                                            $rs = new RegSession;
                                            $rs->regID = $r->regID;
                                            $rs->eventID = $eventID;
                                            $rs->personID = $p->personID;
                                            $rs->sessionID = $sessID;
                                            $rs->confDay = 1;
                                            $rs->creatorID = auth()->user()->id;
                                            $rs->updaterID = auth()->user()->id;
                                            $rs->save();
                                        }
                                    }
                                }
                                //dd(get_defined_vars());
                            }
                        }); // end of Excel chunk bit...
//                    }); // end of database transaction
/*                } catch(\Exception $exception) {
                    Session::flash('alert-danger', 'The event data failed to load properly.');
                    $events = Event::where([
                        ['orgID', '=', $this->currentPerson->defaultOrgID]
                    ])->get();
                    return view('v1.auth_pages.organization.data_upload', compact('events', 'exception'));
                }
*/
                break;

        }
        if($what == 'mbrdata'){
            $what = 'Member records';
        } else {
            $what = 'Event registration records';
        }

        request()->session()->flash('alert-success', "$what were successfully loaded. (" . $this->counter . ")" );
        $events = Event::where([
            ['orgID', '=', $this->currentPerson->defaultOrgID]
        ])->get();

        return view('v1.auth_pages.organization.data_upload', compact('events'));
    }

    public
    function edit ($id) {
        // responds to GET /blah/id/edit and shows the add/edit form
    }

    public
    function update (Request $request, $id) {
        // responds to PATCH /blah/id
    }

    public
    function destroy ($id) {
        // responds to DELETE /blah/id
    }
}

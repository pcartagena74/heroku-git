<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 0);

use App\Address;
use App\Email;
use App\Event;
use App\OrgPerson;
use App\Person;
use App\PersonStaging;
use App\Phone;
use App\RegFinance;
use App\Registration;
use App\RegSession;
use App\Ticket;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel as Excel;
use Rap2hpoutre\FastExcel\FastExcel;

class UploadController extends Controller
{
    public $starttime;
    public $phone_master;
    public $email_master;
    public $address_master;
    public $person_staging_master;

    public function __construct()
    {
        $this->middleware('auth');
        $this->starttime = microtime(true);
    }

    public function index()
    {
        // displays the data upload form

        $today = Carbon::now();
        $user  = Person::find(auth()->user()->id);

        $events = Event::where([
            ['orgID', '=', $user->defaultOrgID],
            ['eventStartDate', '<', $today],
        ])->withCount('registrations')
            ->orderBy('eventStartDate', 'desc')
            ->get();

        return view('v1.auth_pages.organization.data_upload', compact('events'));
    }

    public function show($id)
    {
        // responds to GET /blah/id
    }

    public function create()
    {
        // responds to /blah/create and shows add/edit form
    }

    public function rowsConsumer()
    {
        foreach ($this->rowConsumer as $var) {
            // $key1=>$value1
            // $var = array();
            // foreach ($value as $key1 => $value1) {
            //     $key1       = strtolower(str_replace(' ', '_', $key1));
            //     $var[$key1] = $value1;
            // }
            yield $var;
        }
    }

    public function store(Request $request)
    {
        // responds to POST to /blah and creates, adds, stores the event

        $this->counter       = 0;
        $this->currentPerson = Person::find(auth()->user()->id);
        $what                = request()->input('data_type');
        $filename            = $_FILES['filename']['tmp_name'];

        $tmp_path = request()->file('filename')->store('', 'local');
        $path     = storage_path('app') . '/' . $tmp_path;
        //dd($path);
        $eventID = request()->input('eventID');

        if ($what == 'evtdata' && ($eventID === null || $eventID == trans('messages.admin.select'))) {
            // go back with message
            return Redirect::back()->with('alert-warning', trans('messages.errors.event'));
        }

        switch ($what) {
            case 'mbrdata':

                $collection                  = (new FastExcel)->import($path);
                $currentPerson               = Person::where('personID', auth()->user()->id)->get();
                $currentPerson               = (object) $currentPerson[0]->toArray();
                $rows                        = $this->rowsConsumer();
                $count                       = 0;
                $log                         = array();
                $this->person_staging_master = [];
                foreach ($collection as $key => $value) {
                    $var = array();
                    foreach ($value as $key1 => $value1) {
                        $key1       = strtolower(str_replace(' ', '_', $key1));
                        $var[$key1] = $value1;
                    }
                    $this->storeImportDataDB($var, $currentPerson, $count);
                    $count++;
                }
                $this->bulkInsertAll();
                    PersonStaging::insertIgnore($this->person_staging_master);
                $this->person_staging_master = [];
                // previously used method
                // $import = new MembersImport();
                // try {
                //     $this->counter = $import->import($path);
                //     $this->counter = \Excel::import(new MembersImport, $path);

                // } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                //     $failures = $e->failures();

                //     foreach ($failures as $failure) {
                //         // $failure->row(); // row that went wrong
                //         // $failure->attribute(); // either heading key (if using heading row concern) or column index
                //         // $failure->errors(); // Actual error messages from Laravel validator
                //         // $failure->values(); // The values of the row that has failed.
                //         request()->session()->flash('alert-warning', $failure->row(), $failure->values());
                //     }
                // }

                break;

            case 'evtdata':
                //                try {
                //                    DB::transaction(function () {
                $filename = $_FILES['filename']['tmp_name'];
                Excel::load($filename, function ($reader) {
                    $results = $reader->get();

                    $eventID = request()->input('eventID');
                    $tktID   = Ticket::where('eventID', '=', $eventID)->first();
                    if ($tktID === null) {
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
                    $orgID = $this->currentPerson->defaultOrgID;
                    $rows  = $results->toArray();

                    foreach ($rows as $row) {
                        $this->counter++;
                        $create_user = 0;
                        foreach (array_keys($row) as $k) {
                            //dd($row);
                            switch (1) {
                                case preg_match('/attended/i', $k):
                                    break;
                                case preg_match('/^ticket$/i', $k):
                                    $tktTxt = $row[$k];
                                    /*
                                    if (preg_match('/Bundle/i', $tktTxt)) {
                                    $ticketID = 126;
                                    } elseif (preg_match('/Friday Only/i', $tktTxt)) {
                                    $ticketID = 123;
                                    } elseif (preg_match('/Saturday Only/i', $tktTxt)) {
                                    $ticketID = 124;
                                    } elseif (preg_match('/Friday evening/i', $tktTxt)) {
                                    $ticketID = 125;
                                    }
                                     */
                                    break;
                                case preg_match('/salutation|prefix/i', $k):
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
                                    if (preg_match('/[no|0]/i', $pmtRecd)) {
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
                                case (preg_match('/(pmi.number|pmi.membership|number|membership)/i', $k)):
                                    $pmiID = $row[$k];
                                    if (!is_numeric($pmiID)) {
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
                                    if (preg_match('/Yes/i', $hasPMP)) {
                                        $hasPMP = 1;
                                    } else {
                                        $hasPMP = 0;
                                    }
                                    break;
                                case (preg_match('/first.time/i', $k)):
                                    $firstEvent = $row[$k];
                                    if ($firstEvent !== null) {
                                        $firstEvent = 1;
                                    } else {
                                        $firstEvent = 0;
                                    }
                                    if (preg_match('/[no|0]/i', $firstEvent)) {
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
                                    if ($canPDU === null) {
                                        $canPDU = 0;
                                    } else {
                                        $canPDU = 1;
                                    }
                                    if (preg_match('/[no|0]/i', $canPDU)) {
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
                                    if ($canNtwk === null) {
                                        $canNtwk = 0;
                                    } else {
                                        $canNtwk = 1;
                                    }
                                    if (preg_match('/[no|0]/i', $canNtwk)) {
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
                                    echo ("Encountered an unknown column: '" . $k . "'<br>");
                                    break;
                            }
                        }
                        //dd(get_defined_vars());
                        /*
                        if ($eventID == 97 && ($ticketID == 123 || $ticketID == 126)) {
                        if (preg_match('/^AGILE/i', $f1)) {
                        $fs1 = 91;
                        } elseif (preg_match('/^PORTFOLIO/i', $f1)) {
                        $fs1 = 92;
                        } elseif (preg_match('/^RISK/i', $f1)) {
                        $fs1 = 93;
                        } else {
                        $fs1 = null;
                        }
                        if (preg_match('/^AGILE/i', $f2)) {
                        $fs2 = 96;
                        } elseif (preg_match('/^PORTFOLIO/i', $f2)) {
                        $fs2 = 97;
                        } elseif (preg_match('/^RISK/i', $f2)) {
                        $fs2 = 98;
                        } else {
                        $fs2 = null;
                        }
                        if (preg_match('/^AGILE/i', $f3)) {
                        $fs3 = 101;
                        } elseif (preg_match('/^PORTFOLIO/i', $f3)) {
                        $fs3 = 102;
                        } elseif (preg_match('/^RISK/i', $f3)) {
                        $fs3 = 103;
                        } else {
                        $fs3 = null;
                        }
                        }
                        if ($eventID == 97 && ($ticketID == 124 || $ticketID == 126)) {
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
                         */

                        if (!isset($canNtwk)) {
                            $canNtwk = 0;
                        }
                        if (!isset($allergy)) {
                            $allergy = 0;
                        }
                        if (!isset($pmiID)) {
                            $pmiID = null;
                        }
                        if (!isset($canPDU)) {
                            $canPDU = 0;
                        }
                        if (!isset($firstEvent)) {
                            $firstEvent = 0;
                        }
                        if (!isset($firstEvent)) {
                            $firstEvent = 0;
                        }
                        if (!isset($pmtRecd)) {
                            $pmtRecd = 0;
                        }
                        if (!isset($specialNeeds)) {
                            $specialNeeds = null;
                        }
                        if (!isset($coName)) {
                            $coName = null;
                        }
                        if (!isset($indName)) {
                            $indName = null;
                        }
                        if (!isset($hear)) {
                            $hear = null;
                        }
                        if (!isset($questions)) {
                            $questions = null;
                        }
                        if (!isset($commute)) {
                            $commute = null;
                        }
                        if (!isset($topics)) {
                            $topics = null;
                        }
                        if (!isset($prefName)) {
                            $prefName = null;
                        }
                        if (!isset($experience)) {
                            $experience = null;
                        }
                        // foreach cycle through $row's keys() and switch on preg_match
                        // do the following things to input data:
                        // 1. set the following items to lower case: email addresses
                        // 2. perform date formatting to get into database if needed

                        // Then perform these steps:
                        // 1. Check if $row->email or $row->pmiID is in person-email or org-person tables
                        // 2. If found, get $person and $orgperson record and:
                        //    a. validate or change defaultOrgID on person
                        //    b. validate or add an org-person record for the current orgID
                        //    c. do NOT change first or last name if PMI ID exists but if first names don't match consider preferred name
                        //    d. Add to existing $person record any non-null values
                        // 4. If not found, create new $person, $org-person and $email records
                        // 5. Create new event-registration and regFinance records and give regFinance record the same regID

                        //dd(get_defined_vars());
                        if ($pmiID !== null) {
                            // record of PMI member
                            $op = OrgPerson::where('OrgStat1', '=', $pmiID)->first();
                            if ($op !== null) {
                                // record of PMI member found in DB and matches provided PMI ID
                                $create_user     = 0;
                                $p               = Person::find($op->personID);
                                $p->defaultOrgID = $orgID;
                                $p->updaterID    = $this->currentPerson->personID;
                                if ($experience != null && $p->experience === null) {
                                    $p->experience = $experience;
                                }
                                $p->save();

                                $e = Email::where('emailADDR', '=', $email)->withTrashed()->first();
                                if ($e === null) {
                                    $e            = new Email;
                                    $e->personID  = $p->personID;
                                    $e->emailADDR = $email;
                                    $e->creatorID = $this->currentPerson->personID;
                                    $e->save();
                                }
                            } elseif ($email !== null) {
                                $e = Email::where('emailADDR', '=', $email)->withTrashed()->first();
                                if ($e !== null) {
                                    // record of registrant found, by $email, in DB but no $pmiID provided with reg
                                    $create_user     = 0;
                                    $p               = Person::find($e->personID);
                                    $p->defaultOrgID = $orgID;
                                    $p->updaterID    = $this->currentPerson->personID;
                                    if ($experience != null && $p->experience === null) {
                                        $p->experience = $experience;
                                    }
                                    $p->save();

                                    if ($p->firstName != $first || $p->lastName != $last || $p->compName !== $coName) {
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
                                        if (isset($affiliation)) {
                                            $ps->affiliation = $affiliation;
                                        } else {
                                            $ps->affiliation = 'MassBay';
                                        }
                                        $ps->allergenInfo = $allergy;
                                        $ps->creatorID    = $this->currentPerson->personID;
                                        $ps->save();
                                    }
                                    $op = OrgPerson::where([
                                        ['personID', '=', $p->personID],
                                        ['orgID', '=', $orgID],
                                    ])->first();
                                    // If this Person didn't have an org-person record for this orgID
                                    if ($op === null) {
                                        $op            = new OrgPerson;
                                        $op->personID  = $p->personID;
                                        $op->orgID     = $orgID;
                                        $op->OrgStat1  = $pmiID;
                                        $op->creatorID = $this->currentPerson->personID;
                                        $op->save();
                                    } else {
                                        // update PMI ID if one doesn't already exist
                                        if ($op->OrgStat1 === null) {
                                            $op->OrgStat1  = $pmiID;
                                            $op->updaterID = $this->currentPerson->personID;
                                            $op->save();
                                        }
                                    }
                                } else {
                                    // Didn't find registrant by provided PMI ID or email address
                                    $create_user     = 1;
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
                                    if (isset($affiliation)) {
                                        $p->affiliation = $affiliation;
                                    } else {
                                        $p->affiliation = 'PMI MassBay';
                                    }
                                    $p->allergenInfo = $allergy;
                                    $p->creatorID    = $this->currentPerson->personID;
                                    if ($experience != null) {
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
                            if ($email !== null) {
                                $e = Email::where('emailADDR', '=', $email)->first();

                                if ($e !== null) {
                                    // record of this email in DB
                                    $create_user     = 0;
                                    $p               = Person::find($e->personID);
                                    $p->defaultOrgID = $orgID;
                                    $p->updaterID    = $this->currentPerson->personID;
                                    $p->save();

                                    if ($p->firstName != $first || $p->lastName != $last || $p->compName !== $coName) {
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
                                        if (isset($affiliation)) {
                                            $ps->affiliation = $affiliation;
                                        } else {
                                            $ps->affiliation = 'PMI MassBay';
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
                                        if (isset($affiliation)) {
                                            $p->affiliation = $affiliation;
                                        } else {
                                            $p->affiliation = 'PMI MassBay';
                                        }
                                        $p->allergenInfo = $allergy;
                                        $p->creatorID    = $this->currentPerson->personID;
                                        $p->save();
                                    }
                                    $op = OrgPerson::where([
                                        ['personID', '=', $p->personID],
                                        ['orgID', '=', $orgID],
                                    ])->first();
                                    if ($op === null) {
                                        // need to create a stub org-person record if one doesn't already exist
                                        $op            = new OrgPerson;
                                        $op->personID  = $p->personID;
                                        $op->orgID     = $orgID;
                                        $op->creatorID = $this->currentPerson->personID;
                                        $op->save();
                                    }
                                } else {
                                    // record of this email not in DB
                                    $create_user     = 1;
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
                                    if (isset($affiliation)) {
                                        $p->affiliation = $affiliation;
                                    } else {
                                        $p->affiliation = 'PMI MassBay';
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
                                // $pmiID was null & $email was null
                            }
                        }
                        if ($create_user) {
                            $u        = new User;
                            $u->id    = $p->personID;
                            $u->login = $p->login;
                            $u->email = $p->login;
                            $u->save();
                            $create_user = 0;
                        }
                        // do the rest of the processing for this row
                        if ($eventID == 97) {
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
                        if (isset($affiliation)) {
                            $r->affiliation = $affiliation;
                        } else {
                            $r->affiliation = 'PMI MassBay';
                        }
                        $r->regStatus    = $status;
                        $r->referalText  = $hear;
                        $r->registeredBy = $regBy;
                        $r->discountCode = $disCode;
                        $r->origcost     = number_format($cost, 2, '.', '');
                        $r->subtotal     = number_format($cost, 2, '.', '');
                        if (preg_match('/Non/', $tktTxt)) {
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

                        if ($eventID == 97) {
                            // try to decrement ticket regCounts
                            if ($ticketID == 126) {
                                $t = Ticket::find(123);
                                $t->regCount++;
                                $t->save();
                                $t = Ticket::find(124);
                                $t->regCount++;
                                $t->save();
                                $t = Ticket::find(125);
                                $t->regCount++;
                                $t->save();
                            } else {
                                $t = Ticket::find($ticketID);
                                $t->regCount++;
                                $t->save();
                            }

                            foreach (array($fs1, $fs2, $fs3) as $sessID) {
                                if ($sessID !== null) {
                                    $rs            = new RegSession;
                                    $rs->regID     = $r->regID;
                                    $rs->eventID   = $eventID;
                                    $rs->personID  = $p->personID;
                                    $rs->sessionID = $sessID;
                                    $rs->confDay   = 1;
                                    $rs->creatorID = auth()->user()->id;
                                    $rs->updaterID = auth()->user()->id;
                                    $rs->save();
                                }
                            }
                            foreach (array($ss1, $ss2, $ss3) as $sessID) {
                                if ($sessID !== null) {
                                    $rs            = new RegSession;
                                    $rs->regID     = $r->regID;
                                    $rs->eventID   = $eventID;
                                    $rs->personID  = $p->personID;
                                    $rs->sessionID = $sessID;
                                    $rs->confDay   = 1;
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
        /*

        // Replaced by trans of what below
        if ($what == 'mbrdata') {
        $what = 'Member records';
        } else {
        $what = 'Event registration records';
        }
         */

        // request()->session()->flash('alert-success', trans('messages.admin.upload.loaded',
        //                            ['what' => trans('messages.admin.upload.'.$what), 'count' => $this->counter]));
        $events = Event::where([
            ['orgID', '=', $this->currentPerson->defaultOrgID],
        ])->get();

        return view('v1.auth_pages.organization.data_upload', compact('events'));
    }

    public function store2(Request $request)
    {
        // responds to POST to /blah and creates, adds, stores the event

        $this->counter       = 0;
        $this->currentPerson = Person::find(auth()->user()->id);
        $what                = request()->input('data_type');
        $filename            = $_FILES['filename']['tmp_name'];
        $eventID             = request()->input('eventID');

        if ($what == 'evtdata' && ($eventID === null || $eventID == 'Select an event...')) {
            // go back with message
            return Redirect::back()->with('alert-warning', trans('messages.errors.event'));
        }

        switch ($what) {
            case 'mbrdata':
//                try {
                DB::transaction(function () {
                    $filename = $_FILES['filename']['tmp_name'];
                    Excel::load($filename, function ($reader) {
                        $results = $reader->get();
                        $orgID   = $this->currentPerson->defaultOrgID;
                        foreach ($results as $row) {
                            $process = 1;
                            $this->counter++;
                            // columns in this sheet are fixed; check directly and then add if not found...
                            // foreach $row, search on $row->pmi_id, then $row->primary_email, then $row->alternate_email
                            // if found, get $person, $org-person, $email, $address, $phone records and update, else create

                            $pmi_id = trim($row->pmi_id);
                            $prefix = trim(ucwords($row->prefix));

                            // First & Last Name string detection of all-caps.  But do not ucwords all entries just in case "DeFrancesco" type names exist
                            $f = trim($row->first_name);
                            if ($f == strtoupper($f)) {
                                $first = trim(ucwords($row->first_name));
                            } else {
                                $first = $f;
                            }
                            $l = trim(ucwords($row->last_name));
                            if ($l == strtoupper($l)) {
                                $last = trim(ucwords($row->last_name));
                            } else {
                                $last = $l;
                            }
                            $midName  = trim(ucwords($row->middle_name));
                            $suffix   = trim(ucwords($row->suffix));
                            $title    = trim(ucwords($row->title));
                            $compName = trim(ucwords($row->company));

                            $op     = OrgPerson::where('OrgStat1', '=', (integer) $pmi_id)->first();
                            $em1    = trim(strtolower($row->primary_email));
                            $em2    = trim(strtolower($row->alternate_email));
                            $emchk1 = Email::whereRaw('lower(emailADDR) = ?', [$em1])->first();
                            if ($emchk1 !== null) {
                                $chk1 = $emchk1->emailADDR;
                            }
                            $emchk2 = Email::whereRaw('lower(emailADDR) = ?', [$em2])->first();
                            if ($emchk2 !== null) {
                                $chk2 = $emchk2->emailADDR;
                            }
                            $pchk = Person::where([
                                ['firstName', '=', $first],
                                ['lastName', '=', $last],
                            ])->first();

                            if ($em1 === null && $em2 === null) {
                                // If no email addresses are provided, a user account cannot be creatable.
                                break;
                            }

                            if ($op === null && $emchk1 === null && $emchk2 === null && $pchk === null) {
                                // PMI ID, first & last names, and emails are not found so person is likely completely new; create all records

                                $p               = new Person;
                                $u               = new User;
                                $p->prefix       = $prefix;
                                $p->firstName    = $first;
                                $p->prefName     = $first;
                                $p->midName      = $midName;
                                $p->lastName     = $last;
                                $p->suffix       = $suffix;
                                $p->title        = $title;
                                $p->compName     = $compName;
                                $p->creatorID    = auth()->user()->id;
                                $p->defaultOrgID = $this->currentPerson->defaultOrgID;
                                $process         = 0;

                                // If email1 is not null or blank, use it as primary to login, etc.
                                if ($em1 !== null && $em1 != "" && $em1 != " ") {
                                    $p->login = $em1;
                                    $p->save();
                                    $u->id    = $p->personID;
                                    $u->login = $em1;
                                    $u->name  = $em1;
                                    $u->email = $em1;
                                    $u->save();

                                    $e            = new Email;
                                    $e->personID  = $p->personID;
                                    $e->emailADDR = $em1;
                                    $e->isPrimary = 1;
                                    $e->creatorID = auth()->user()->id;
                                    $e->updaterID = auth()->user()->id;
                                    $e->save();

                                    // Otherwise, try with email #2
                                } elseif ($em2 !== null && $em2 != '' && $em2 != ' ') {
                                    $p->login = $em2;
                                    $p->save();
                                    $u->id    = $p->personID;
                                    $u->login = $em2;
                                    $u->name  = $em2;
                                    $u->email = $em2;
                                    $u->save();

                                    $e            = new Email;
                                    $e->personID  = $p->personID;
                                    $e->emailADDR = $em2;
                                    $e->isPrimary = 1;
                                    $e->creatorID = auth()->user()->id;
                                    $e->updaterID = auth()->user()->id;
                                    try {
                                        $e->save();
                                    } catch (\Exception $exception) {
                                        // There was an error with saving the email -- likely an integrity constraint.
                                    }
                                } else {
                                    // This is a last resort when there are no email addresses associated with the record
                                    // Better to abandon; avoid $p->save();
                                    // Technically, should not ever get here because we check ahead of time.
                                    break;
                                }

                                $newOP            = new OrgPerson;
                                $newOP->orgID     = $p->defaultOrgID;
                                $newOP->personID  = $p->personID;
                                $newOP->OrgStat1  = (integer) $row->pmi_id;
                                $newOP->OrgStat2  = trim(ucwords($row->chapter_member_class));
                                $newOP->RelDate1  = Carbon::createFromFormat('d/m/Y', $row->pmi_join_date)->toDateTimeString();
                                $newOP->RelDate2  = Carbon::createFromFormat('d/m/Y', $row->chapter_join_date)->toDateTimeString();
                                $newOP->RelDate3  = Carbon::createFromFormat('d/m/Y', $row->pmi_expiration)->toDateTimeString();
                                $newOP->RelDate4  = Carbon::createFromFormat('d/m/Y', $row->chapter_expiration)->toDateTimeString();
                                $newOP->creatorID = auth()->user()->id;
                                $newOP->save();

                                // If email 1 existed and was used as primary but email 2 was also provided, add it.
                                if ($em1 !== null && $em2 !== null && $em2 != $em1 && $em2 != "" && $em2 != " " && $em2 != $chk2) {
                                    $e            = new Email;
                                    $e->personID  = $p->personID;
                                    $e->emailADDR = $em2;
                                    $e->creatorID = auth()->user()->id;
                                    $e->updaterID = auth()->user()->id;
                                    try {
                                        $e->save();
                                    } catch (\Exception $exception) {
                                        // There was an error with saving the email -- likely an integrity constraint.
                                    }
                                }
                            } elseif ($op !== null) {
                                // There was an org-person record (found by $OrgStat1 == PMI ID)
                                $newOP           = $op;
                                $newOP->OrgStat2 = trim(ucwords($row->chapter_member_class));
                                if (isset($row->pmi_join_date)) {
                                    $newOP->RelDate1 = Carbon::createFromFormat('d/m/Y', $row->pmi_join_date)->toDateTimeString();
                                }
                                if (isset($row->chapter_join_date)) {
                                    $newOP->RelDate2 = Carbon::createFromFormat('d/m/Y', $row->chapter_join_date)->toDateTimeString();
                                }
                                if (isset($row->pmi_expiration)) {
                                    $newOP->RelDate3 = Carbon::createFromFormat('d/m/Y', $row->pmi_expiration)->toDateTimeString();
                                }
                                if (isset($row->pmi_expiration)) {
                                    $newOP->RelDate4 = Carbon::createFromFormat('d/m/Y', $row->chapter_expiration)->toDateTimeString();
                                }
                                $newOP->updaterID = auth()->user()->id;
                                $newOP->save();

                                $p = Person::find($newOP->personID);
                                if ($em1 !== null && $em1 != "" && $em1 != " " && $em1 != $chk1) {
                                    $e            = new Email;
                                    $e->personID  = $p->personID;
                                    $e->emailADDR = $em1;
                                    $e->isPrimary = 1;
                                    $e->creatorID = auth()->user()->id;
                                    $e->updaterID = auth()->user()->id;
                                    $e->save();
                                }
                                if ($em2 !== null && $em2 != "" && $em2 != " " && $em2 != $chk2 && $em2 != $em1) {
                                    $e            = new Email;
                                    $e->personID  = $p->personID;
                                    $e->emailADDR = $em2;
                                    $e->isPrimary = 1;
                                    $e->creatorID = auth()->user()->id;
                                    $e->updaterID = auth()->user()->id;
                                    $e->save();
                                }
                            } elseif ($emchk1 !== null && $em1 !== null && $em1 != '' && $em1 != ' ') {
                                // email1 was found in the database
                                $p  = Person::find($emchk1->personID);
                                $op = OrgPerson::where([
                                    ['personID', $p->personID],
                                    ['orgID', $p->defaultOrgID],
                                ])->first();
                            } elseif ($emchk2 !== null && $em2 !== null && $em2 != '' && $em2 != ' ') {
                                // email2 was found in the database
                                $p  = Person::find($emchk2->personID);
                                $op = OrgPerson::where([
                                    ['personID', $p->personID],
                                    ['orgID', $p->defaultOrgID],
                                ])->first();
                            }

                            if ($process) {
                                $p->prefix       = $prefix;
                                $p->firstName    = $first;
                                $p->prefName     = $first;
                                $p->midName      = $midName;
                                $p->lastName     = $last;
                                $p->suffix       = $suffix;
                                $p->title        = $title;
                                $p->compName     = $compName;
                                $p->updaterID    = auth()->user()->id;
                                $p->defaultOrgID = $this->currentPerson->defaultOrgID;
                                $p->save();
                            }

                            // Add the person-specific records

                            $addr = Address::where('addr1', '=', trim(ucwords($row->preferred_address)))->get();
                            if ($addr === null && $row->preferred_address !== null && $row->preferred_address != "" && $row->preferred_address != " ") {
                                $addr           = new Address;
                                $addr->personID = $p->personID;
                                $addr->addrTYPE = trim(ucwords($row->preferred_address_type));
                                $addr->addr1    = trim(ucwords($row->preferred_address));
                                $addr->city     = trim(ucwords($row->city));
                                $addr->state    = trim(ucwords($row->state));
                                $z              = (integer) trim($row->zip);
                                if (strlen($z) == 4) {
                                    $z = "0" . $z;
                                } elseif (strlen($z) == 8) {
                                    $r2 = substr($z, -4);
                                    $l2 = substr($z, 4);
                                    $z  = "0" . $l2 . "-" . $r2;
                                }
                                $addr->zip = $z;

                                // Need a smarter way to determine country code
                                if (trim(ucwords($row->country)) == 'United States') {
                                    $addr->cntryID = 228;
                                } elseif (trim(ucwords($row->country)) == 'Canada') {
                                    $addr->cntryID = 36;
                                }
                                $addr->creatorID = auth()->user()->id;
                                $addr->updaterID = auth()->user()->id;
                                $addr->save();
                            }

                            $fone = Phone::where([
                                ['phoneNumber', '=', $row->home_phone],
                            ])->first();

                            if ($row->home_phone !== null && $fone === null) {
                                $fone              = new Phone;
                                $fone->personID    = $p->personID;
                                $fone->phoneNumber = (integer) $row->home_phone;
                                $fone->phoneType   = 'Home';
                                $fone->creatorID   = auth()->user()->id;
                                $fone->updaterID   = auth()->user()->id;
                                $fone->save();
                            }

                            $fone = Phone::where([
                                ['phoneNumber', '=', $row->work_phone],
                            ])->first();

                            if ($row->work_phone !== null && $row->work_phone != $row->home_phone && $fone === null) {
                                $fone              = new Phone;
                                $fone->personID    = $p->personID;
                                $fone->phoneNumber = (integer) $row->work_phone;
                                $fone->phoneType   = 'Work';
                                $fone->creatorID   = auth()->user()->id;
                                $fone->updaterID   = auth()->user()->id;
                                $fone->save();
                            }

                            $fone = Phone::where([
                                ['phoneNumber', '=', $row->mobile_phone],
                            ])->first();

                            if ($row->mobile_phone !== null && $row->mobile_phone != $row->work_phone
                                && $row->mobile_phone != $row->home_phone && $fone === null) {
                                $fone              = new Phone;
                                $fone->personID    = $p->personID;
                                $fone->phoneNumber = (integer) $row->mobile_phone;
                                $fone->phoneType   = 'Mobile';
                                $fone->creatorID   = auth()->user()->id;
                                $fone->updaterID   = auth()->user()->id;
                                $fone->save();
                            }

                            $ps               = new PersonStaging;
                            $ps->prefix       = $prefix;
                            $ps->firstName    = $first;
                            $ps->midName      = $midName;
                            $ps->lastName     = $last;
                            $ps->suffix       = $suffix;
                            $ps->login        = $p->login;
                            $ps->title        = $title;
                            $ps->compName     = $compName;
                            $ps->defaultOrgID = $this->currentPerson->defaultOrgID;
                            $ps->creatorID    = auth()->user()->id;
                            $ps->save();
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

            case 'evtdata':
//                try {
                //                    DB::transaction(function () {
                $filename = $_FILES['filename']['tmp_name'];
                Excel::load($filename, function ($reader) {
                    $results = $reader->get();

                    $eventID = request()->input('eventID');
                    $tktID   = Ticket::where('eventID', '=', $eventID)->first();
                    if ($tktID === null) {
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
                    $orgID = $this->currentPerson->defaultOrgID;
                    $rows  = $results->toArray();

                    foreach ($rows as $row) {
                        $this->counter++;
                        $create_user = 0;
                        foreach (array_keys($row) as $k) {
                            //dd($row);
                            switch (1) {
                                case preg_match('/attended/i', $k):
                                    break;
                                case preg_match('/^ticket$/i', $k):
                                    $tktTxt = $row[$k];
                                    /*
                                    if (preg_match('/Bundle/i', $tktTxt)) {
                                    $ticketID = 126;
                                    } elseif (preg_match('/Friday Only/i', $tktTxt)) {
                                    $ticketID = 123;
                                    } elseif (preg_match('/Saturday Only/i', $tktTxt)) {
                                    $ticketID = 124;
                                    } elseif (preg_match('/Friday evening/i', $tktTxt)) {
                                    $ticketID = 125;
                                    }
                                     */
                                    break;
                                case preg_match('/salutation|prefix/i', $k):
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
                                    if (preg_match('/[no|0]/i', $pmtRecd)) {
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
                                case (preg_match('/(pmi.number|pmi.membership|number|membership)/i', $k)):
                                    $pmiID = $row[$k];
                                    if (!is_numeric($pmiID)) {
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
                                    if (preg_match('/Yes/i', $hasPMP)) {
                                        $hasPMP = 1;
                                    } else {
                                        $hasPMP = 0;
                                    }
                                    break;
                                case (preg_match('/first.time/i', $k)):
                                    $firstEvent = $row[$k];
                                    if ($firstEvent !== null) {
                                        $firstEvent = 1;
                                    } else {
                                        $firstEvent = 0;
                                    }
                                    if (preg_match('/[no|0]/i', $firstEvent)) {
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
                                    if ($canPDU === null) {
                                        $canPDU = 0;
                                    } else {
                                        $canPDU = 1;
                                    }
                                    if (preg_match('/[no|0]/i', $canPDU)) {
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
                                    if ($canNtwk === null) {
                                        $canNtwk = 0;
                                    } else {
                                        $canNtwk = 1;
                                    }
                                    if (preg_match('/[no|0]/i', $canNtwk)) {
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
                                    echo ("Encountered an unknown column: '" . $k . "'<br>");
                                    break;
                            }
                        }
                        //dd(get_defined_vars());
                        /*
                        if ($eventID == 97 && ($ticketID == 123 || $ticketID == 126)) {
                        if (preg_match('/^AGILE/i', $f1)) {
                        $fs1 = 91;
                        } elseif (preg_match('/^PORTFOLIO/i', $f1)) {
                        $fs1 = 92;
                        } elseif (preg_match('/^RISK/i', $f1)) {
                        $fs1 = 93;
                        } else {
                        $fs1 = null;
                        }
                        if (preg_match('/^AGILE/i', $f2)) {
                        $fs2 = 96;
                        } elseif (preg_match('/^PORTFOLIO/i', $f2)) {
                        $fs2 = 97;
                        } elseif (preg_match('/^RISK/i', $f2)) {
                        $fs2 = 98;
                        } else {
                        $fs2 = null;
                        }
                        if (preg_match('/^AGILE/i', $f3)) {
                        $fs3 = 101;
                        } elseif (preg_match('/^PORTFOLIO/i', $f3)) {
                        $fs3 = 102;
                        } elseif (preg_match('/^RISK/i', $f3)) {
                        $fs3 = 103;
                        } else {
                        $fs3 = null;
                        }
                        }
                        if ($eventID == 97 && ($ticketID == 124 || $ticketID == 126)) {
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
                         */

                        if (!isset($canNtwk)) {
                            $canNtwk = 0;
                        }
                        if (!isset($allergy)) {
                            $allergy = 0;
                        }
                        if (!isset($pmiID)) {
                            $pmiID = null;
                        }
                        if (!isset($canPDU)) {
                            $canPDU = 0;
                        }
                        if (!isset($firstEvent)) {
                            $firstEvent = 0;
                        }
                        if (!isset($firstEvent)) {
                            $firstEvent = 0;
                        }
                        if (!isset($pmtRecd)) {
                            $pmtRecd = 0;
                        }
                        if (!isset($specialNeeds)) {
                            $specialNeeds = null;
                        }
                        if (!isset($coName)) {
                            $coName = null;
                        }
                        if (!isset($indName)) {
                            $indName = null;
                        }
                        if (!isset($hear)) {
                            $hear = null;
                        }
                        if (!isset($questions)) {
                            $questions = null;
                        }
                        if (!isset($commute)) {
                            $commute = null;
                        }
                        if (!isset($topics)) {
                            $topics = null;
                        }
                        if (!isset($prefName)) {
                            $prefName = null;
                        }
                        if (!isset($experience)) {
                            $experience = null;
                        }
                        // foreach cycle through $row's keys() and switch on preg_match
                        // do the following things to input data:
                        // 1. set the following items to lower case: email addresses
                        // 2. perform date formatting to get into database if needed

                        // Then perform these steps:
                        // 1. Check if $row->email or $row->pmiID is in person-email or org-person tables
                        // 2. If found, get $person and $orgperson record and:
                        //    a. validate or change defaultOrgID on person
                        //    b. validate or add an org-person record for the current orgID
                        //    c. do NOT change first or last name if PMI ID exists but if first names don't match consider preferred name
                        //    d. Add to existing $person record any non-null values
                        // 4. If not found, create new $person, $org-person and $email records
                        // 5. Create new event-registration and regFinance records and give regFinance record the same regID

                        //dd(get_defined_vars());
                        if ($pmiID !== null) {
                            // record of PMI member
                            $op = OrgPerson::where('OrgStat1', '=', $pmiID)->first();
                            if ($op !== null) {
                                // record of PMI member found in DB and matches provided PMI ID
                                $create_user     = 0;
                                $p               = Person::find($op->personID);
                                $p->defaultOrgID = $orgID;
                                $p->updaterID    = $this->currentPerson->personID;
                                if ($experience != null && $p->experience === null) {
                                    $p->experience = $experience;
                                }
                                $p->save();

                                $e = Email::where('emailADDR', '=', $email)->withTrashed()->first();
                                if ($e === null) {
                                    $e            = new Email;
                                    $e->personID  = $p->personID;
                                    $e->emailADDR = $email;
                                    $e->creatorID = $this->currentPerson->personID;
                                    $e->save();
                                }
                            } elseif ($email !== null) {
                                $e = Email::where('emailADDR', '=', $email)->withTrashed()->first();
                                if ($e !== null) {
                                    // record of registrant found, by $email, in DB but no $pmiID provided with reg
                                    $create_user     = 0;
                                    $p               = Person::find($e->personID);
                                    $p->defaultOrgID = $orgID;
                                    $p->updaterID    = $this->currentPerson->personID;
                                    if ($experience != null && $p->experience === null) {
                                        $p->experience = $experience;
                                    }
                                    $p->save();

                                    if ($p->firstName != $first || $p->lastName != $last || $p->compName !== $coName) {
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
                                        if (isset($affiliation)) {
                                            $ps->affiliation = $affiliation;
                                        } else {
                                            $ps->affiliation = 'MassBay';
                                        }
                                        $ps->allergenInfo = $allergy;
                                        $ps->creatorID    = $this->currentPerson->personID;
                                        $ps->save();
                                    }
                                    $op = OrgPerson::where([
                                        ['personID', '=', $p->personID],
                                        ['orgID', '=', $orgID],
                                    ])->first();
                                    // If this Person didn't have an org-person record for this orgID
                                    if ($op === null) {
                                        $op            = new OrgPerson;
                                        $op->personID  = $p->personID;
                                        $op->orgID     = $orgID;
                                        $op->OrgStat1  = $pmiID;
                                        $op->creatorID = $this->currentPerson->personID;
                                        $op->save();
                                    } else {
                                        // update PMI ID if one doesn't already exist
                                        if ($op->OrgStat1 === null) {
                                            $op->OrgStat1  = $pmiID;
                                            $op->updaterID = $this->currentPerson->personID;
                                            $op->save();
                                        }
                                    }
                                } else {
                                    // Didn't find registrant by provided PMI ID or email address
                                    $create_user     = 1;
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
                                    if (isset($affiliation)) {
                                        $p->affiliation = $affiliation;
                                    } else {
                                        $p->affiliation = 'PMI MassBay';
                                    }
                                    $p->allergenInfo = $allergy;
                                    $p->creatorID    = $this->currentPerson->personID;
                                    if ($experience != null) {
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
                            if ($email !== null) {
                                $e = Email::where('emailADDR', '=', $email)->first();

                                if ($e !== null) {
                                    // record of this email in DB
                                    $create_user     = 0;
                                    $p               = Person::find($e->personID);
                                    $p->defaultOrgID = $orgID;
                                    $p->updaterID    = $this->currentPerson->personID;
                                    $p->save();

                                    if ($p->firstName != $first || $p->lastName != $last || $p->compName !== $coName) {
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
                                        if (isset($affiliation)) {
                                            $ps->affiliation = $affiliation;
                                        } else {
                                            $ps->affiliation = 'PMI MassBay';
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
                                        if (isset($affiliation)) {
                                            $p->affiliation = $affiliation;
                                        } else {
                                            $p->affiliation = 'PMI MassBay';
                                        }
                                        $p->allergenInfo = $allergy;
                                        $p->creatorID    = $this->currentPerson->personID;
                                        $p->save();
                                    }
                                    $op = OrgPerson::where([
                                        ['personID', '=', $p->personID],
                                        ['orgID', '=', $orgID],
                                    ])->first();
                                    if ($op === null) {
                                        // need to create a stub org-person record if one doesn't already exist
                                        $op            = new OrgPerson;
                                        $op->personID  = $p->personID;
                                        $op->orgID     = $orgID;
                                        $op->creatorID = $this->currentPerson->personID;
                                        $op->save();
                                    }
                                } else {
                                    // record of this email not in DB
                                    $create_user     = 1;
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
                                    if (isset($affiliation)) {
                                        $p->affiliation = $affiliation;
                                    } else {
                                        $p->affiliation = 'PMI MassBay';
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
                                // $pmiID was null & $email was null
                            }
                        }
                        if ($create_user) {
                            $u        = new User;
                            $u->id    = $p->personID;
                            $u->login = $p->login;
                            $u->email = $p->login;
                            $u->save();
                            $create_user = 0;
                        }
                        // do the rest of the processing for this row
                        if ($eventID == 97) {
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
                        if (isset($affiliation)) {
                            $r->affiliation = $affiliation;
                        } else {
                            $r->affiliation = 'PMI MassBay';
                        }
                        $r->regStatus    = $status;
                        $r->referalText  = $hear;
                        $r->registeredBy = $regBy;
                        $r->discountCode = $disCode;
                        $r->origcost     = number_format($cost, 2, '.', '');
                        $r->subtotal     = number_format($cost, 2, '.', '');
                        if (preg_match('/Non/', $tktTxt)) {
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

                        if ($eventID == 97) {
                            // try to decrement ticket regCounts
                            if ($ticketID == 126) {
                                $t = Ticket::find(123);
                                $t->regCount++;
                                $t->save();
                                $t = Ticket::find(124);
                                $t->regCount++;
                                $t->save();
                                $t = Ticket::find(125);
                                $t->regCount++;
                                $t->save();
                            } else {
                                $t = Ticket::find($ticketID);
                                $t->regCount++;
                                $t->save();
                            }

                            foreach (array($fs1, $fs2, $fs3) as $sessID) {
                                if ($sessID !== null) {
                                    $rs            = new RegSession;
                                    $rs->regID     = $r->regID;
                                    $rs->eventID   = $eventID;
                                    $rs->personID  = $p->personID;
                                    $rs->sessionID = $sessID;
                                    $rs->confDay   = 1;
                                    $rs->creatorID = auth()->user()->id;
                                    $rs->updaterID = auth()->user()->id;
                                    $rs->save();
                                }
                            }
                            foreach (array($ss1, $ss2, $ss3) as $sessID) {
                                if ($sessID !== null) {
                                    $rs            = new RegSession;
                                    $rs->regID     = $r->regID;
                                    $rs->eventID   = $eventID;
                                    $rs->personID  = $p->personID;
                                    $rs->sessionID = $sessID;
                                    $rs->confDay   = 1;
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
        if ($what == 'mbrdata') {
            $what = 'Member records';
        } else {
            $what = 'Event registration records';
        }

        request()->session()->flash('alert-success', "$what were successfully loaded. (" . $this->counter . ")");
        $events = Event::where([
            ['orgID', '=', $this->currentPerson->defaultOrgID],
        ])->get();

        return view('v1.auth_pages.organization.data_upload', compact('events'));
    }

    public function edit($id)
    {
        // responds to GET /blah/id/edit and shows the add/edit form
    }

    public function update(Request $request, $id)
    {
        // responds to PATCH /blah/id
    }

    public function destroy($id)
    {
        // responds to DELETE /blah/id
    }
    //
    public function timeMem($msg = null)
    {
        return;
        $m   = (1024 * 1024);
        $t   = ((microtime(true) - $this->starttime));
        $str = '';
        if (!empty($msg)) {
            $str = $msg;
        }
        $str .= " Time: " . ($t) . ", Memory Usage :" . round((memory_get_usage() / $m), 3);
        echo $str . "<br>\n";
        // $str .=  round((memory_get_peak_usage() / $m), 2) . "<br>\n";
    }
    public function storeImportDataDB($row, $currentPerson, $count_g)
    {
        // DB::connection()->disableQueryLog();

        // $this->timeMem('starttime ' . $count_g);
        $count = 0;
        $count++;
        $update_existing_record = 1;
        $chk1                   = null;
        $chk2                   = null;
        $p                      = null;
        $need_op_record         = 0;
        $pchk                   = null;
        $op                     = null;
        $addr                   = null;
        $fone                   = null;
        $pmi_id                 = null;
        $u                      = null;
        $f                      = null;
        $l                      = null;
        // columns in the MemberDetail sheet are fixed; check directly and then add if not found...
        // foreach $row, search on $row->pmi_id, then $row->primary_email, then $row->alternate_email
        // if found, get $person, $org-person, $email, $address, $phone records and update, else create

        $pmi_id = trim($row['pmi_id']);

        //merging org-person two queies into one as it will be more light weight
        // $op = DB::table('org-person')->where(['OrgStat1' => $pmi_id])->get();
        // $this->timeMem('1 op query ');
        $op = new Collection();

        // create index on OrgStat1
        $any_op = DB::table('org-person')->where('OrgStat1', $pmi_id)->get();
        // $this->timeMem('1 any op query ');

        if ($any_op->isNotEmpty()) {
            foreach ($any_op as $key => $value) {
                if ($value->orgID == $currentPerson->defaultOrgID) {
                    $op = new Collection($value);
                    break;
                }
            }
        }

        $prefix = trim(ucwords($row['prefix']));
        // First & Last Name string detection of all-caps or all-lower.
        // Do not ucwords all entries just in case "DeFrancesco" type names exist
        $f = trim($row['first_name']);
        if ($f == strtoupper($f) || $f == strtolower($f)) {
            $first = ucwords($f);
        } else {
            $first = $f;
        }

        $l = trim($row['last_name']);
        if ($l == strtoupper($l) || $l == strtolower($l)) {
            $last = ucwords($l);
        } else {
            $last = $l;
        }

        $midName  = trim(ucwords($row['middle_name']));
        $suffix   = trim(ucwords($row['suffix']));
        $title    = trim(ucwords($row['title']));
        $compName = trim(ucwords($row['company']));

        $em1    = trim(strtolower($row['primary_email']));
        $em2    = trim(strtolower($row['alternate_email']));
        $emchk1 = new Collection();
        $emchk2 = new Collection();

        if (filter_var($em1, FILTER_VALIDATE_EMAIL) && filter_var($em2, FILTER_VALIDATE_EMAIL)) {
            $email_check = Email::whereRaw('lower(emailADDR) = ?', [$em1])
                ->whereRaw('lower(emailADDR) = ?', [$em2])
                ->withTrashed()->limit(1)->get();
            if ($email_check->isNotEmpty()) {
                foreach ($email_check as $key => $value) {
                    if ($value == $em1) {
                        $emchk1 = new collection($value);
                    } else {
                        $emchk2 = new collection($value);
                    }
                }
            }
        } elseif (filter_var($em1, FILTER_VALIDATE_EMAIL)) {
            $emchk1 = Email::whereRaw('lower(emailADDR) = ?', [$em1])->withTrashed()->limit(1)->get();
            if ($emchk1->isNotEmpty()) {
                $chk1 = $emchk1[0];
            }
        } elseif (filter_var($em2, FILTER_VALIDATE_EMAIL)) {
            $emchk2 = Email::whereRaw('lower(emailADDR) = ?', [$em2])->withTrashed()->limit(1)->get();
            if ($emchk2->isNotEmpty()) {
                $chk2 = $emchk2[0];
            }
        }
        if (!filter_var($em1, FILTER_VALIDATE_EMAIL)) {
            $em1 = null;
        }
        if (!filter_var($em2, FILTER_VALIDATE_EMAIL)) {
            $em2 = null;
        }

        $pchk = Person::where(['firstName' => $first, 'lastName' => $last])->limit(1)->get();
        // $this->timeMem('5 $pchk ');

        if ($op->isEmpty() && $any_op->isEmpty() && $emchk1->isEmpty() && $emchk2->isEmpty() && $pchk->isEmpty()) {

            // PMI ID, first & last names, and emails are not found so person is likely completely new; create all records

            $need_op_record = 1;
            $p              = '';
            $u              = '';

            $p_array = [
                'prefix'       => $prefix,
                'firstName'    => $first,
                'prefName'     => $first,
                'midName'      => $midName,
                'lastName'     => $last,
                'suffix'       => $suffix,
                'title'        => $title,
                'compName'     => $compName,
                'creatorID'    => auth()->user()->id,
                'defaultOrgID' => $currentPerson->defaultOrgID,
                'affiliation'  => $currentPerson->affiliation,
            ];
            $update_existing_record = 0;

            // If email1 is not null or blank, use it as primary to login, etc.
            if ($em1 !== null && $em1 != "" && $em1 != " ") {
                $p_array['login'] = $em1;
                $p                = Person::create($p_array);
                // $this->timeMem('6 p insert');
                // $p->login = $em1;
                // $p->save();
                $u_array = [
                    'id'    => $p->personID,
                    'login' => $em1,
                    'name'  => $em1,
                    'email' => $em1,
                ];
                $u = User::create($u_array);
                // $this->timeMem('7 u insert');
                $this->insertEmail($personID = $p->personID, $email = $em1, $primary = 1);

                // Otherwise, try with email #2
            } elseif ($em2 !== null && $em2 != '' && $em2 != ' ' && $p->login === null) {
                $p->login = $em2;
                $p->save();
                $u->id    = $p->personID;
                $u->login = $em2;
                $u->name  = $em2;
                $u->email = $em2;
                $u->save();
                $this->insertEmail($personID = $p->personID, $email = $em2, $primary = 1);
                try {

                } catch (\Exception $exception) {
                    // There was an error with saving the email -- likely an integrity constraint.
                }
            } elseif ($pchk !== null) {
                // I don't think this code can actually run.
                // The $pchk check in the outer loop is what this should have been.

                // Emails didn't match for some reason but found a first/last name match
                // Recheck to see if there's just 1 match
                // no need to query again as we donot have filter for now
                $p = $pchk[0];
                // $pchk_count = Person::where([
                //     ['firstName', '=', $first],
                //     ['lastName', '=', $last],
                // ])->get();
                // $this->timeMem('8 $pchk_count');
                // if (count($pchk_count) == 1) {
                //     $p = $pchk;
                // } else {
                //     // Would need a way to pick the right one if there's more than 1
                //     // For now, just taking the first one
                //     $p = $pchk;
                // }
            } else {
                // This is a last resort when there are no email addresses associated with the record
                // Better to abandon; avoid $p->save();
                // Technically, should not ever get here because we check ahead of time.
                // break;
            }

            // If email 1 exists and was used as primary but email 2 was also provided and unique, add it.
            if ($em1 !== null && $em2 !== null && $em2 != $em1 && $em2 != "" && $em2 != " " && $em2 != $chk2) {
                $this->insertEmail($personID = $p->personID, $email = $em2, $primary = 0);
            } elseif ($em2 !== null && $em2 == strtolower($chk2)) {
                if ($emchk2->personID != $p->personID) {
                    $emchk2->debugNote = "ugh!  Was: $emchk2->personID; Should be: $p->personID";
                    $emchk2->personID  = $p->personID;
                    $emchk2->save();
                    // $this->timeMem('9 $pchk_count update 2130');
                }

            }

        } elseif ($op->isNotEmpty() || $any_op->isNotEmpty()) {
            // There was an org-person record (found by $OrgStat1 == PMI ID) for this chapter/orgID
            if ($op->isNotEmpty()) {
                // For modularity, updating the $op record will happen below as there are no dependencies
                // $p = Person::where(['personID' => $op[0]->personID])->get();
                //
                $p = Person::where(['personID' => $op->get('personID')])->get();
                // $this->timeMem('10 op and any op check 2142');
                $p = $p[0];
            } else {
                $need_op_record = 1;
                // $p              = Person::where(['personID' => $any_op[0]->personID])->get();
                $p = Person::where(['personID' => $any_op->get(['personID'])])->get();
                // $this->timeMem('11 op and any op check 2148');
                $p = $p[0];
            }

            // We have an $org-person record so we should NOT rely on firstName/lastName matching at all
            $pchk = null;

            // Because we should have found a person record, determine if we should create and associate email records
            if ($em1 !== null && $em1 != "" && $em1 != " " && $em1 != strtolower($chk1) && $em1 != strtolower($chk2)) {
                $this->insertEmail($personID = $p->personID, $email = $em1, $primary = 0);
            } elseif ($em1 !== null && $em1 == strtolower($chk1)) {
                if ($emchk1[0]->personID != $p->personID) {
                    $emchk1[0]->personID  = $p->personID;
                    $emchk1[0]->debugNote = "ugh!  Was: $emchk1[0]->personID; Should be: $p->personID";
                    DB::table('person-email')->where(['personID' => $emchk1[0]->personID])
                        ->update(['personID' => $p->personID, 'debugNote' => $emchk1[0]->debugNote]);
                    // $emchk1->save();
                    // $this->timeMem('12 update email 2163');
                }
            }
            if ($em2 !== null && $em2 != "" && $em2 != " " && $em2 != strtolower($chk1) && $em2 != strtolower($chk2) && $em2 != $em1) {
                $this->insertEmail($personID = $p->personID, $email = $em2, $primary = 0);
            } elseif ($em2 !== null && $em2 == strtolower($chk2)) {
                if ($emchk2->personID != $p->personID) {
                    $emchk2->debugNote = "ugh!  Was: $emchk2->personID; Should be: $p->personID";
                    $emchk2->personID  = $p->personID;
                    DB::table('person-email')->where(['personID' => $emchk2[0]->personID])
                        ->update(['personID' => $p->personID, 'debugNote' => $emchk2[0]->debugNote]);
                    // $emchk2->save();
                    // $this->timeMem('13 update email 2173');
                }
            }
            // } elseif ($emchk1->isNotEmpty() && $em1->isNotEmpty() && $em1 != '' && $em1 != ' ') {
        } elseif ($emchk1->isNotEmpty() && !empty($em1) && $em1 != '' && $em1 != ' ') {
            // email1 was found in the database, but either no PMI ID match in DB, possibly due to a different/incorrect entry
            $p = Person::where(['personID' => $emchk1->personID])->get();
            // $this->timeMem('14 get person 2180');
            $p = $p[0];
            try {
                $op = OrgPerson::where([
                    ['personID', $emchk1->personID],
                    ['orgID', $currentPerson->defaultOrgID],
                ])->get();
                // $this->timeMem('15 get org person 2187');
            } catch (Exception $ex) {
                dd([$emchk1, $em1]);
            }
            if ($op->isEmpty()) {$need_op_record = 1;}
            // We have an email record match so we should NOT rely on firstName/lastName matching at all
            $pchk = null;
        } elseif ($emchk2->isNotEmpty() && !empty($em2) && $em2 != '' && $em2 != ' ') {
            // email2 was found in the database
            // $p  = Person::where(['personID' => $emchk2->personID])->get();
            // $p  = $p[0];
            $op = OrgPerson::where([
                ['personID', $emchk2->personID],
                ['orgID', $currentPerson->defaultOrgID],
            ])->get();
            // $this->timeMem('16 get org person 2202');
            if ($op->isEmpty()) {$need_op_record = 1;}
            // We have an email record match so we should NOT rely on firstName/lastName matching at all
            $pchk = null;
        } elseif ($pchk->isNotEmpty()) {
            // Everything else was null but firstName & lastName matches someone
            $p                      = $pchk;
            $update_existing_record = 1;

            // Should check if there are multiple firstName/lastName matches and then decide what, if anything,
            // can be done to pick the right one...
            if (!empty($p->personID)) {
                $op = OrgPerson::where([
                    ['personID', $p->personID],
                    ['orgID', $currentPerson->defaultOrgID],
                ])->get();
                // $this->timeMem('17 get org person 2218');
                if ($op->isEmpty()) {
                    $need_op_record = 1;
                }
            }
        }

        if ($update_existing_record && !empty($p)) {
            $ary = [];
            if (strlen($prefix) > 0) {
                $ary['prefix'] = $prefix;
            }
            $ary['firstName'] = $first;
            try {
                if ($p->prefName === null) {
                    $ary['prefName'] = $first;
                }
                if (strlen($midName) > 0) {
                    $ary['midName'] = $midName;
                }
                $ary['lastName'] = $last;
                if (strlen($suffix) > 0) {
                    $ary['suffix'] = $suffix;
                }
                if ($p->title === null || $pchk !== null) {
                    $ary['title'] = $title;
                }
                if ($p->compName === null || $pchk !== null) {
                    $ary['compName'] = $compName;
                }
                if ($p->affiliation === null) {
                    $ary['affiliation'] = $currentPerson->affiliation;
                }

                // One day: think about how to auto-populate indName field using compName

                $ary['updaterID']    = auth()->user()->id;
                $ary['defaultOrgID'] = $currentPerson->defaultOrgID;
                DB::table('person')->where('personID', $p->personID)->update($ary);
                // $this->timeMem('18 get org person 2257');

            } catch (Exception $ex) {
                dd($p);
            }

        }

        $memClass  = trim(ucwords($row['chapter_member_class']));
        $pmiRenew  = trim(ucwords($row['pmiauto_renew_status']));
        $chapRenew = trim(ucwords($row['chapter_auto_renew_status']));

        if ($need_op_record) {
            // A new OP record must be created because EITHER:
            // 1. the member is completely new to the system or
            // 2. the member is in the system but under another chapter/orgID
            $newOP           = new OrgPerson;
            $newOP->orgID    = $p->defaultOrgID;
            $newOP->personID = $p->personID;
            $newOP->OrgStat1 = $pmi_id;

            if (strlen($memClass) > 0) {
                $newOP->OrgStat2 = $memClass;
            }
            // Because OrgStat3 & OrgStat4 data has 'Yes' or blanks as values
            if (strlen($pmiRenew) > 0) {
                if ($pmiRenew != "Yes") {
                    $newOP->OrgStat3 = "No";
                } else {
                    $newOP->OrgStat3 = $pmiRenew;
                }
            }

            if (strlen($chapRenew) > 0) {
                if ($chapRenew != "Yes") {
                    $newOP->OrgStat4 = "No";
                } else {
                    $newOP->OrgStat4 = $chapRenew;
                }
            }

            if (isset($row['pmi_join_date'])) {
                $newOP->RelDate1 = Carbon::createFromFormat('d/m/Y', $row['pmi_join_date'])->toDateTimeString();
            }
            if (isset($row['chapter_join_date'])) {
                $newOP->RelDate2 = Carbon::createFromFormat('d/m/Y', $row['chapter_join_date'])->toDateTimeString();
            }
            if (isset($row['pmi_expiration'])) {
                $newOP->RelDate3 = Carbon::createFromFormat('d/m/Y', $row['pmi_expiration'])->toDateTimeString();
            }
            if (isset($row['pmi_expiration'])) {
                $newOP->RelDate4 = Carbon::createFromFormat('d/m/Y', $row['chapter_expiration'])->toDateTimeString();
            }
            $newOP->creatorID = auth()->user()->id;
            $newOP->save();
            // $this->timeMem('19 new po update 2312');
            if ($p->defaultOrgPersonID === null) {
                DB::table('person')->where('personID', $p->personID)->update(['defaultOrgPersonID' => $newOP->id]);
                // $this->timeMem('20 person update 2315');
                // $p->defaultOrgPersonID = $newOP->id;
                // $p->save();
            }
        } else {
            // We'll update some fields on the off chance they weren't properly filled in a previous creation
            if (isset($op[0])) {
                $newOP = $op[0];
                // dd($newOP);
                $ary = [];
                if ($newOP->OrgStat1 === null) {
                    $ary['OrgStat1'] = $pmi_id;
                }

                if (strlen($pmiRenew) > 0) {
                    if ($pmiRenew != "Yes") {
                        $ary['OrgStat3'] = "No";
                    } else {
                        $ary['OrgStat3'] = $pmiRenew;
                    }
                }

                if (strlen($chapRenew) > 0) {
                    if ($chapRenew != "Yes") {
                        $ary['OrgStat4'] = "No";
                    } else {
                        $ary['OrgStat4'] = $chapRenew;
                    }
                }
                if (!empty($row['pmi_join_date'])) {
                    $ary['RelDate1'] = Carbon::createFromFormat('d/m/Y', $row['pmi_join_date'])->toDateTimeString();
                }
                if (!empty($row['chapter_join_date'])) {
                    $ary['RelDate2'] = Carbon::createFromFormat('d/m/Y', $row['chapter_join_date'])->toDateTimeString();
                }
                if (!empty($row['pmi_expiration'])) {
                    $ary['RelDate3'] = Carbon::createFromFormat('d/m/Y', $row['pmi_expiration'])->toDateTimeString();
                }
                if (!empty($row['pmi_expiration'])) {
                    $ary['RelDate4'] = Carbon::createFromFormat('d/m/Y', $row['chapter_expiration'])->toDateTimeString();
                }
                $ary['updaterID'] = auth()->user()->id;
                DB::table('org-person')->where('id', $newOP->id)->update($ary);
                $this->timeMem('21 update org person 2358');
                // $newOP->save();
            }
        }

        // Add the person-specific records as needed
        if (!empty($p)) {
            $pa   = trim(ucwords($row['preferred_address']));
            $addr = Address::where(['addr1' => $pa, 'personId' => $p->personID])->limit(1)->get();
            // $this->timeMem('22 get address 2367');
            if ($addr->isEmpty() && $pa !== null && $pa != "" && $pa != " ") {
                $z = trim($row['zip']);
                if (strlen($z) == 4) {
                    $z = "0" . $z;
                } elseif (strlen($z) == 8) {
                    $r2 = substr($z, -4, 4);
                    $l2 = substr($z, 0, 4);
                    $z  = "0" . $l2 . "-" . $r2;
                } elseif (strlen($z) == 9) {
                    $r2 = substr($z, -4, 4);
                    $l2 = substr($z, 0, 5);
                    $z  = $l2 . "-" . $r2;
                }
                // $addr->zip = $z;

                // // Need a smarter way to determine country code
                $cntry    = trim(ucwords($row['country']));
                $cntry_id = 228;
                if ($cntry == 'United States') {
                    $addr->cntryID = 228;
                    $cntry_id      = 228;
                } elseif ($cntry == 'Canada') {
                    $addr->cntryID = 36;
                    $cntry_id      = 36;
                }

                $this->insertAddress(
                    $personID = $p->personID,
                    $addresstype = trim(ucwords($row['preferred_address_type'])),
                    $addr1 = trim(ucwords($row['preferred_address'])),
                    $city = trim(ucwords($row['city'])),
                    $state = trim(ucwords($row['state'])),
                    $zip = $z,
                    $country = $cntry_id);
            }
            $num = [];
            if (strlen($row['home_phone']) > 7) {
                $num[] = trim($row['home_phone']);
            }

            if (strlen($row['work_phone']) > 7) {
                $num[] = trim($row['work_phone']);
            }

            if (strlen($row['mobile_phone']) > 7) {
                $num[] = trim($row['mobile_phone']);
            }

            if (!empty($num)) {
                // $phone = Phone::whereIn('phoneNumber', $num)->get();
                $phone = DB::table('person-phone')->whereIn('phoneNumber', $num)->get();
                // $this->timeMem('23 get phone 2419');
                if ($phone->isNotEmpty()) {
                    foreach ($phone as $key => $value) {
                        // $value->debugNote = "ugh!  Was: $value->personID; Should be: $p->personID";
                        // $value->personID  = $p->personID;
                        // $value->save();
                    }
                } else {
                    if (strlen($row['home_phone']) > 7) {
                        $this->insertPhone($personid = $p->personID, $phonenumber = $row['home_phone'], $phonetype = 'Home');
                    }

                    if (strlen($row['work_phone']) > 7) {
                        $this->insertPhone($personid = $p->personID, $phonenumber = $row['work_phone'], $phonetype = 'Work');
                    }

                    if (strlen($row['mobile_phone']) > 7) {
                        $this->insertPhone($personid = $p->personID, $phonenumber = $row['mobile_phone'], $phonetype = 'Mobile');
                    }
                }
            }

            $this->insertPersonStaging($p->personID, $prefix, $first, $midName, $last, $suffix, $p->login, $title, $compName, $currentPerson->defaultOrgID);
        }
        unset($chk1);
        unset($chk2);
        unset($p);
        unset($u);
        unset($f);
        unset($l);
        unset($e);
        unset($need_op_record);
        unset($pchk);
        unset($op);
        unset($addr);
        unset($fone);
        unset($pmi_id);
        unset($fone);
        unset($newOP);
        unset($emchk1);
        unset($emchk2);
        unset($ps);
        unset($row);

        // $this->bulkInsertAll();5863 baki me kuch problem h 
        // 
        gc_collect_cycles();
    }

    public function insertPersonStaging($personID, $prefix, $first, $midName, $lastname, $suffix, $login, $title, $compName, $default_org)
    {
        $this->person_staging_master[] = [
            'personID'     => $personID,
            'prefix'       => $prefix,
            'firstName'    => $first,
            'midName'      => $midName,
            'lastName'     => $lastname,
            'suffix'       => $suffix,
            'login'        => $login,
            'title'        => $title,
            'compName'     => $compName,
            'defaultOrgID' => $default_org,
            'creatorID'    => auth()->user()->id,
        ];
    }
    public function insertAddress($personID, $addresstype, $addr1, $city, $state, $zip, $country)
    {
        //it has creatorID and UpdaterID user auth user id
        // $addr           = new Address;
        // $addr->personID = $p->personID;
        // $addr->addrTYPE = trim(ucwords($row['preferred_address_type']));
        // $addr->addr1    = trim(ucwords($row['preferred_address']));
        // $addr->city     = trim(ucwords($row['city']));
        // $addr->state    = trim(ucwords($row['state']));
        // $z              = trim($row['zip']);
        // $addr->zip      = $z;

        // // Need a smarter way to determine country code
        // $cntry = trim(ucwords($row['country']));
        // if ($cntry == 'United States') {
        //     $addr->cntryID = 228;
        // } elseif ($cntry == 'Canada') {
        //     $addr->cntryID = 36;
        // }
        // $addr->creatorID = auth()->user()->id;
        // $addr->updaterID = auth()->user()->id;
        // $addr->save();
        $this->address_master[] = [
            'personID'  => $personID,
            'addrTYPE'  => $addresstype,
            'addr1'     => $addr1,
            'city'      => $city,
            'state'     => $state,
            'zip'       => $zip,
            'cntryID'   => $country,
            'creatorID' => auth()->user()->id,
            'updaterID' => auth()->user()->id,
        ];
    }
    /**
     * create bulk array for phone number insertion
     * @param  int $personID    [person id]
     * @param  numeric $phoneNumber [phone number]
     * @param  string $phoneType   [home work mobile]
     * @return null
     */
    public function insertPhone($personID, $phoneNumber, $phoneType)
    {
        //it has creatorID and UpdaterID user auth user id
        $this->phone_master[] = [
            'personID'    => $personID,
            'phoneNumber' => $phoneNumber,
            'phoneType'   => $phoneType,
            'creatorID'   => auth()->user()->id,
            'updaterID'   => auth()->user()->id,
        ];

    }

    /**
     * create bulk insert array for email
     * @param  integer  $personID
     * @param  string  $email
     * @param  integer $primary
     * @return [type]
     */
    public function insertEmail($personID, $email, $primary = 0)
    {
        //it has creatorID and UpdaterID user auth user id
        // $e            = new Email;
        //             $e->personID  = $p->personID;
        //             $e->emailADDR = $em1;
        //             $e->isPrimary = 1;
        //             $e->creatorID = auth()->user()->id;
        //             $e->updaterID = auth()->user()->id;
        //             $e->save();

        //it has creatorID and UpdaterID user auth user id
        $this->email_master[] = [
            'personID'  => $personID,
            'emailADDR' => $email,
            'isPrimary' => $primary,
            'creatorID' => auth()->user()->id,
            'updaterID' => auth()->user()->id,
        ];

    }

    public function bulkInsertAll()
    {

        if (!empty($this->email_master)) {
            try {
                Email::insertIgnore($this->email_master);
            } catch (Exception $ex) {
                //do nothing
            }
            // $this->timeMem('24 inset bulk email ' . count($this->email_master));
        }

        if (!empty($this->phone_master)) {
            try {
                Phone::insertIgnore($this->phone_master);
            } catch (Exception $ex) {
                //do nothing
            }
            // $this->timeMem('25 insert bulk phone ' . count($this->phone_master));
        }

        if (!empty($this->address_master)) {
            try {
                Address::insertIgnore($this->address_master);
            } catch (Exception $ex) {
                //do nothing
            }
            // $this->timeMem('26 inset bulk addres ' . count($this->address_master));
        }

        if (!empty($this->person_staging_master)) {
            // PersonStaging::insertIgnore($this->person_staging_master);
            // $this->timeMem('27 inset bulk personstaggin ' . count($this->person_staging_master));
        }

        $this->email_master   = array();
        $this->phone_master   = array();
        $this->address_master = array();
        // $this->person_staging_master = array();

    }

}

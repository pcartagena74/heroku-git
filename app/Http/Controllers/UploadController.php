<?php

namespace App\Http\Controllers;

use App\Address;
use App\OrgPerson;
use App\Person;
use App\Event;
use App\Email;
use App\User;
use App\Phone;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

use Illuminate\Http\Request;

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
        ])->get();

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

        $this->currentPerson = Person::find(auth()->user()->id);
        $what     = request()->input('data_type');
        $eventID  = request()->input('eventID');
        $filename = $_FILES['filename']['tmp_name'];


        switch ($what) {
            case 'mbrdata':
                Excel::filter('chunk')->load($filename)->chunk(50, function($results) {
                    $blah = 0;
                    foreach($results as $row) {
                        // columns in this sheet are fixed; check directly and then add if not found...
                        // foreach $row, search on $row->pmi_id, then $row->primary_email, then $row->alternate_email
                        // if found, get $person, $org-person, $email, $address, $phone records and update, else create

                        //dd($row);
                        $op = OrgPerson::where('OrgStat1', '=', (integer)$row->pmi_id)->first();
                        $emchk1= Email::where('emailADDR', '=', $row->primary_email)->first();
                        $emchk2= Email::where('emailADDR', '=', $row->alternate_email)->first();
                        $em1 = trim(strtolower($row->primary_email)); $em2 = trim(strtolower($row->alternate_email));

                        if($op === null && $emchk1 === null && $emchk2 === null){
                            // person is completely new; create all records

                            $p = new Person;  $u = new User;
                            $p->prefix = trim(ucwords($row->prefix));
                            $p->firstName = trim(ucwords($row->first_name));
                            $p->midName = trim(ucwords($row->middle_name));
                            $p->lastName = trim(ucwords($row->last_name));
                            $p->suffix = trim(ucwords($row->suffix));
                            $p->title = trim(ucwords($row->title));
                            $p->compName = trim(ucwords($row->company));
                            $p->creatorID = auth()->user()->id;
                            $p->defaultOrgID = $this->currentPerson->defaultOrgID;
                            if($em1 !== null){
                                $p->login = $em1;
                                $p->save();
                                $u->id = $p->personID;
                                $u->login = $em1;
                                $u->email = $em1;
                                $u->save();

                                $e = new Email;
                                $e->personID = $p->personID;
                                $e->emailADDR = $em1;
                                $e->isPrimary = 1;
                                $e->creatorID = auth()->user()->id;
                                $e->updaterID = auth()->user()->id;
                                $e->save();

                            } elseif($em2 !== null){
                                $p->login = $em2;
                                $p->save();
                                $u->id = $p->personID;
                                $u->login = $em2;
                                $u->email = $em2;
                                $u->save();

                                $e = new Email;
                                $e->personID = $p->personID;
                                $e->emailADDR = $em2;
                                $e->isPrimary = 1;
                                $e->creatorID = auth()->user()->id;
                                $e->updaterID = auth()->user()->id;
                                $e->save();
                            }
                            $p->save();

                            $newOP = new OrgPerson;
                            $newOP->orgID = $p->defaultOrgID;
                            $newOP->personID = $p->personID;
                            $newOP->OrgStat1 = (integer)$row->pmi_id;
                            $newOP->OrgStat2 = trim(ucwords($row->chapter_member_class));
                            $newOP->RelDate1 = Carbon::createFromFormat('d/m/Y', $row->pmi_join_date)->toDateTimeString();
                            $newOP->RelDate2 = Carbon::createFromFormat('d/m/Y', $row->chapter_join_date)->toDateTimeString();
                            $newOP->RelDate3 = Carbon::createFromFormat('d/m/Y', $row->pmi_expiration)->toDateTimeString();
                            $newOP->RelDate4 = Carbon::createFromFormat('d/m/Y', $row->chapter_expiration)->toDateTimeString();
                            $newOP->creatorID = auth()->user()->id;
                            $newOP->save();

                            $addr = new Address;
                            $addr->personID = $p->personID;
                            $addr->addrTYPE = trim(ucwords($row->preferred_address_type));
                            $addr->addr1 = trim(ucwords($row->preferred_address));
                            $addr->city = trim(ucwords($row->city));
                            $addr->state = trim(ucwords($row->state));
                            $z = (integer) trim(ucwords($row->zip));
                            if(strlen($z)==4) {
                                $z = "0" . $z;
                            } elseif (strlen($z)==8) {
                                $r2 = substr($z, -4);
                                $l2 = substr($z, 4);
                                $z = "0" . $l2 . "-" . $r2;
                            }
                            $addr->zip = $z;
                            if(trim(ucwords($row->country)) == 'United States'){
                                $addr->cntryID = 228;
                            }
                            $addr->creatorID = auth()->user()->id;
                            $addr->updaterID = auth()->user()->id;
                            $addr->save();

                            if($em1 !== null && $em2 !== null){
                                $e = new Email;
                                $e->personID = $p->personID;
                                $e->emailADDR = $em2;
                                $e->creatorID = auth()->user()->id;
                                $e->updaterID = auth()->user()->id;
                                $e->save();
                            }

                            if($row->home_phone !== null){
                                $fone = new Phone;
                                $fone->personID = $p->personID;
                                $fone->phoneNumber = (integer) $row->home_phone;
                                $fone->phoneType = 'Home';
                                $fone->creatorID = auth()->user()->id;
                                $fone->updaterID = auth()->user()->id;
                                $fone->save();
                            }

                            if($row->work_phone !== null){
                                $fone = new Phone;
                                $fone->personID = $p->personID;
                                $fone->phoneNumber = (integer) $row->work_phone;
                                $fone->phoneType = 'Work';
                                $fone->creatorID = auth()->user()->id;
                                $fone->updaterID = auth()->user()->id;
                                $fone->save();
                            }

                            if($row->mobile_phone !== null){
                                $fone = new Phone;
                                $fone->personID = $p->personID;
                                $fone->phoneNumber = (integer) $row->mobile_phone;
                                $fone->phoneType = 'Mobile';
                                $fone->creatorID = auth()->user()->id;
                                $fone->updaterID = auth()->user()->id;
                                $fone->save();
                            }
                        }
                        $blah = $blah + count($results);
                    }
                });
                break;

            case 'evtdata':
                Excel::filter('chunk')->load($filename)->chunk(50, function($results) {
                    foreach($results as $row) {
                        dd($row);
                        // foreach cycle through $row's keys() and switch on preg_match
                        // do the following things to input data:
                        // 1. set the following items to lower case: email addresses
                        // 2. perform date formatting to get into database if needed

                        // Then perform these steps:
                        // 1. Check if $row->email is in person-email table
                        // 2. If found, get $person and $orgperson record and:
                        //    a. validate or change defaultOrgID on person
                        //    b. validate or add an org-person record for the current orgID
                        //    c. do NOT change first or last name if PMI ID exists but if first names don't match consider preferred name
                        //    d. Add to existing $person record any non-null values
                        // 4. If not found, create new $person, $org-person and $email records
                        // 5. Create new event-registration and regFinance records and give regFinance record the same regID
                    }
                });
                break;

        }

        $events = Event::where([
            ['orgID', '=', $this->currentPerson->defaultOrgID]
        ])->get();

        return view('v1.auth_pages.organization.data_upload', compact('blah', 'events'));
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

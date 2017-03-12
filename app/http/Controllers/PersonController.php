<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Address;
use App\Email;
use App\Person;
use App\User;

class PersonController extends Controller
{
    public function __construct () {
        $this->middleware('auth');
    }

    public function index () {
        // responds to /blah; This is for member management page
        $this->currentPerson = Person::find(auth()->user()->id);
        $topBits             = [];
        $total_people        = DB::table('person')
                                 ->join('org-person', 'org-person.personID', '=', 'person.personID')
                                 ->where([
                                     ['person.personID', '!=', 1],
                                     ['org-person.orgID', '=', $this->currentPerson->defaultOrgID]
                                 ])->count();
        $individual          = 'Individual';
        $individuals         = DB::table('person')
                                 ->join('org-person', 'org-person.personID', '=', 'person.personID')
                                 ->where([
                                     ['person.personID', '!=', 1],
                                     ['OrgStat2', '=', $individual],
                                     ['org-person.orgID', '=', $this->currentPerson->defaultOrgID]
                                 ])->count();
        $student             = 'Student';
        $students            = DB::table('person')
                                 ->join('org-person', 'org-person.personID', '=', 'person.personID')
                                 ->where([
                                     ['person.personID', '!=', 1],
                                     ['OrgStat2', '=', $student],
                                     ['org-person.orgID', '=', $this->currentPerson->defaultOrgID]
                                 ])->count();
        $retiree             = 'Retiree';
        $retirees            = DB::table('person')
                                 ->join('org-person', 'org-person.personID', '=', 'person.personID')
                                 ->where([
                                     ['person.personID', '!=', 1],
                                     ['OrgStat2', '=', $retiree],
                                     ['org-person.orgID', '=', $this->currentPerson->defaultOrgID]
                                 ])->count();

        array_push($topBits, [3, 'Total People', $total_people, '', '']);
        array_push($topBits, [3, $individual . " Members", $individuals, '', '']);
        array_push($topBits, [3, $retiree . " Members", $retirees, '', '']);
        array_push($topBits, [3, $student . " Members", $students, '', '']);

        $mbr_sql = "SELECT p.personID, concat(firstName, ' ', lastName) AS fullName, OrgStat1, OrgStat2, compName, 
                           title, indName, date_format(RelDate4, '%l/%d/%Y') AS 'RelDate4', 
                           (SELECT count(*) AS 'cnt' FROM `event-registration` er
						 WHERE er.personID=p.personID) AS 'cnt'
					FROM `org-person` o JOIN person p ON o.personID=p.personID
					WHERE o.orgID = ? AND p.personID <>1";

        $mbr_list = DB::select($mbr_sql, [$this->currentPerson->defaultOrgID]);

        return view('v1.auth_pages.members.list', compact('topBits', 'mbr_list'));
    }

    public function show ($id) {
        // responds to GET /blah/id
        $this->currentPerson = Person::find(auth()->user()->id);
        if($id=='my') {
            $id = $this->currentPerson->personID;
        }
        $sql = "SELECT prefix, firstName, midName, lastName, suffix, prefName, u.login, title, compName, indName, 
                    OrgStat1, OrgStat2, OrgStat3, OrgStat4, OrgStat5, OrgStat6, OrgStat7, OrgStat8, OrgStat9, OrgStat10, 
                    date_format(RelDate1, '%c/%e/%Y') as RelDate1, date_format(RelDate2, '%c/%e/%Y') as RelDate2, date_format(RelDate3, '%c/%e/%Y') as RelDate3,
                    date_format(RelDate4, '%c/%e/%Y') as RelDate4, date_format(RelDate5, '%c/%e/%Y') as RelDate5, date_format(RelDate6, '%c/%e/%Y') as RelDate6, 
                    date_format(RelDate7, '%c/%e/%Y') as RelDate7, date_format(RelDate8, '%c/%e/%Y') as RelDate8, date_format(RelDate9, '%c/%e/%Y') as RelDate9, 
                    date_format(RelDate10, '%c/%e/%Y') as RelDate10,
                    OSN1, OSN2, OSN3, OSN4, OSN5, OSN6, OSN7, OSN8, OSN9, OSN10, 
                    ODN1, ODN2, ODN3, ODN4, ODN5, ODN6, RelDate7, ODN8, ODN9, ODN10, p.personID
                FROM `person` p
                JOIN `users` u on u.id = p.personID
                JOIN `org-person` op on op.personID = p.personID
                JOIN `organization` o on op.orgID=o.orgID
                WHERE p.personID = $id";

        $profile = DB::select($sql); $profile = $profile[0];  $topBits = '';

        $prefixes = DB::table('prefixes')->get();

        $industries = DB::table('industries')->get();
        $addrTypes = DB::table('address-type')->get();
        $emailTypes = DB::table('email-type')->get();

        $addresses = Address::where('personID', $id)->select('addrID', 'addrTYPE', 'addr1', 'addr2', 'city', 'state', 'zip', 'cntryID')->get();
        $countries = DB::table('countries')->select('cntryID', 'cntryName')->get();

        $emails = Email::where('personID', $id)->select('emailID', 'emailTYPE', 'emailADDR', 'isPrimary')->get();

        return view('v1.auth_pages.members.profile',
        compact('profile', 'topBits', 'prefixes', 'industries', 'addresses', 'emails', 'addrTypes', 'emailTypes', 'countries'));
    }

    public function create () {
        // responds to /blah/create and shows add/edit form
    }

    public function store (Request $request) {
        // responds to POST to /blah and creates, adds, stores the event
        dd(request()->all());
    }

    public function edit ($id) {
        // responds to GET /blah/id/edit and shows the add/edit form
    }

    public function update (Request $request, $id) {
        // responds to PATCH /blah/id
        $name = request()->input('name');
        $value = request()->input('value');

        if($name == 'login'){
            $user = User::find($id);
            $user->login = $value;
            $user->email = $value;
            $user->save();

            $person = Person::find($id);
            $person->login = $value;
            $person->updaterID = auth()->user()->id;
            $person->save();

        } else {
            $person = Person::find($id);
            $person->{$name} = $value;
            $person->updaterID = auth()->user()->id;
            $person->save();
        }
    }

    public function destroy ($id) {
        // responds to DELETE /blah/id
    }
}

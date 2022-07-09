<?php

namespace App\Traits;

use App\Models\Address;
use App\Models\Email;
use App\Models\OrgPerson;
use App\Models\Person;
use App\Models\PersonStaging;
use App\Models\Phone;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait ExcelMemberImportTrait
{
    public $starttime;
    public $phone_master;
    public $email_master;
    public $address_master;
    public $person_staging_master;
    public $currentPerson;

    public function timeMem($msg = null)
    {
        return;
        $m = (1024 * 1024);
        $t = ((microtime(true) - $this->starttime));
        $str = '';
        if (! empty($msg)) {
            $str = $msg;
        }
        $str .= ' Time: '.($t).', Memory Usage :'.round((memory_get_usage() / $m), 3);
        echo $str."<br>\n";
        // $str .=  round((memory_get_peak_usage() / $m), 2) . "<br>\n";
    }

    public function storeImportDataDB($row, $currentPerson, $import_detail)
    {
        $this->currentPerson = $currentPerson;
        $user = User::where('id', $currentPerson->personID)->get()->first();
        \App::setLocale($user->locale);
        DB::connection()->disableQueryLog();
        $import_detail->refresh();
        // $this->timeMem('starttime ' . $count_g);
        $count = 0;
        $count++;
        $update_existing_record = 1;
        $chk1 = null;
        $chk2 = null;
        $p = null;
        $need_op_record = 0;
        $pchk = null;
        $op = null;
        $addr = null;
        $fone = null;
        $pmi_id = null;
        $u = null;
        $f = null;
        $l = null;
        $has_update = false;
        $has_insert = false;
        // columns in the MemberDetail sheet are fixed; check directly and then add if not found...
        // foreach $row, search on $row->pmi_id, then $row->primary_email, then $row->alternate_email
        // if found, get $person, $org-person, $email, $address, $phone records and update, else create
        $pmi_id = trim($row['pmi_id']);

        //merging org-person two queries into one as it will be more light weight
        // $op = DB::table('org-person')->where(['OrgStat1' => $pmi_id])->get();
        // $this->timeMem('1 op query ');
        $op = null;

        // find any OrgPerson records that might exist with the PMI ID (OrgStat1)
        $any_op = DB::table('org-person')->where('OrgStat1', $pmi_id)->get();
        // $this->timeMem('1 any op query ');
        if ($any_op->isNotEmpty()) {
            foreach ($any_op as $key => $value) {
                if ($value->orgID == $currentPerson->defaultOrgID) {
                    $op = OrgPerson::find($value->id);
                    break;
                }
            }
            $any_op = new Collection($any_op[0]);
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

        $midName = trim(ucwords($row['middle_name']));
        $suffix = trim(ucwords($row['suffix']));
        $title = trim(ucwords($row['title']));
        $compName = trim(ucwords($row['company']));

        $em1 = trim(strtolower($row['primary_email']));
        $em2 = trim(strtolower($row['alternate_email']));
        $emchk1 = new Collection();
        $emchk2 = new Collection();

        if (filter_var($em1, FILTER_VALIDATE_EMAIL) && filter_var($em2, FILTER_VALIDATE_EMAIL)) {
            $email_check = Email::whereRaw('lower(emailADDR) = ?', [$em1])
                ->orWhereRaw('lower(emailADDR) = ?', [$em2])
                ->withTrashed()->get();
            if ($email_check->isNotEmpty()) {
                foreach ($email_check as $key => $value) {
                    if ($value->emailADDR == $em1) {
                        $emchk1 = new collection();
                        $emchk1->push($value);
                    } elseif ($value->emailADDR == $em2) {
                        $emchk2 = new collection();
                        $emchk2->push($value);
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
        if (! filter_var($em1, FILTER_VALIDATE_EMAIL)) {
            $em1 = null;
        }
        if (! filter_var($em2, FILTER_VALIDATE_EMAIL)) {
            $em2 = null;
        }

        $pchk = Person::where(['firstName' => $first, 'lastName' => $last])->limit(1)->get();
        // $this->timeMem('5 $pchk ');
        if ($op === null && $any_op->isEmpty() && $emchk1->isEmpty() && $emchk2->isEmpty() && $pchk->isEmpty()) {

            // PMI ID, first & last names, and emails are not found so person is likely completely new; create all records

            $need_op_record = 1;
            $p = '';
            $u = '';

            $p_array = [
                'prefix'       => $prefix,
                'firstName'    => $first,
                'prefName'     => $first,
                'midName'      => $midName,
                'lastName'     => $last,
                'suffix'       => $suffix,
                'title'        => $title,
                'compName'     => $compName,
                'creatorID'    => $currentPerson->personID,
                'defaultOrgID' => $currentPerson->defaultOrgID,
                'affiliation'  => $currentPerson->affiliation,
            ];
            $update_existing_record = 0;
            // If email1 is not null or blank, use it as primary to login, etc.
            if ($em1 !== null && $em1 != '' && $em1 != ' ') {
                $p_array['login'] = $em1;
                $p = Person::create($p_array);
                $has_insert = true;
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
            } elseif ($em2 !== null && $em2 != '' && $em2 != ' ' && empty($p_array['login'])) {
                $p_array['login'] = $em2;
                $p = Person::create($p_array);
                $has_insert = true;

                $u_array = [
                    'id'    => $p->personID,
                    'login' => $em2,
                    'name'  => $em2,
                    'email' => $em2,
                ];
                $u = User::create($u_array);
                $this->insertEmail($personID = $p->personID, $email = $em2, $primary = 1);
                try {
                } catch (\Exception $exception) {
                    // There was an error with saving the email -- likely an integrity constraint.
                }
            } elseif ($pchk->isNotEmpty()) {
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
                return;
            }

            // If email 1 exists and was used as primary but email 2 was also provided and unique, add it.
            if ($em1 !== null && $em2 !== null && $em2 != $em1 && $em2 != '' && $em2 != ' ' && $em2 != $chk2) {
                $this->insertEmail($personID = $p->personID, $email = $em2, $primary = 0);
            } elseif ($em2 !== null && $em2 == strtolower($chk2)) {
                if ($emchk2->personID != $p->personID) {
                    $emchk2->debugNote = "ugh!  Was: $emchk2->personID; Should be: $p->personID";
                    $emchk2->personID = $p->personID;
                    $emchk2->save();
                    // $this->timeMem('9 $pchk_count update 2130');
                }
            }
        } elseif ($op !== null || $any_op->isNotEmpty()) {
            // There was an org-person record (found by $OrgStat1 == PMI ID) for this chapter/orgID
            if ($op !== null) {
                // For modularity, updating the $op record will happen below as there are no dependencies
                // $p = Person::where(['personID' => $op[0]->personID])->get();
                // $p = DB::table('person')->where(['personID' => $op->get('personID')])->limit(1)->get();
                // dd($p->first());
                // $this->timeMem('10 op and any op check 2142');
                // $p = $p->first();
                $p = Person::find($op->personID);
            } else {
                $need_op_record = 1;
                // $p              = Person::where(['personID' => $any_op[0]->personID])->get();
                $p = DB::table('person')->where(['personID' => $any_op->get('personID')])->limit(1)->get();
                // $this->timeMem('11 op and any op check 2148');
                $p = $p->first();
            }
            if (empty($p->personID)) {
                return;
            }
            // dd(getType($p));
            // We have an $org-person record so we should NOT rely on firstName/lastName matching at all
            $pchk = null;

            // Because we should have found a person record, determine if we should create and associate email records
            if ($em1 !== null && $em1 != '' && $em1 != ' ' && $em1 != strtolower($chk1) && $em1 != strtolower($chk2)) {
                $this->insertEmail($personID = $p->personID, $email = $em1, $primary = 0);
            } elseif ($em1 !== null && $em1 == strtolower($chk1)) {
                if ($emchk1[0]->personID != $p->personID) {
                    $emchk1[0]->personID = $p->personID;
                    $emchk1[0]->debugNote = "ugh!  Was: $emchk1[0]->personID; Should be: $p->personID";
                    DB::table('person-email')->where(['personID' => $emchk1[0]->personID])
                        ->update(['personID' => $p->personID, 'debugNote' => $emchk1[0]->debugNote]);
                    // $emchk1->save();
                    // $this->timeMem('12 update email 2163');
                }
            }
            if ($em2 !== null && $em2 != '' && $em2 != ' ' && $em2 != strtolower($chk1) && $em2 != strtolower($chk2) && $em2 != $em1) {
                $this->insertEmail($personID = $p->personID, $email = $em2, $primary = 0);
            } elseif ($em2 !== null && $em2 == strtolower($chk2)) {
                if ($emchk2->personID != $p->personID) {
                    $emchk2->debugNote = "ugh!  Was: $emchk2->personID; Should be: $p->personID";
                    $emchk2->personID = $p->personID;
                    DB::table('person-email')->where(['personID' => $emchk2[0]->personID])
                        ->update(['personID' => $p->personID, 'debugNote' => $emchk2[0]->debugNote]);
                    // $emchk2->save();
                    // $this->timeMem('13 update email 2173');
                }
            }
            // } elseif ($emchk1->isNotEmpty() && $em1->isNotEmpty() && $em1 != '' && $em1 != ' ') {
        } elseif ($emchk1->isNotEmpty() && ! empty($emchk1[0]) && ! empty($em1) && $em1 != '' && $em1 != ' ') {
            $emchk1 = $emchk1[0];
            // email1 was found in the database, but either no PMI ID match in DB, possibly due to a different/incorrect entry
            $p = Person::where(['personID' => $emchk1->personID])->get();
            // $this->timeMem('14 get person 2180');
            $p = $p[0];
            try {
                $op = OrgPerson::where([
                    ['personID', $emchk1->personID],
                    ['orgID', $currentPerson->defaultOrgID],
                ])->get()->first();
                // $this->timeMem('15 get org person 2187');
                if (empty($op)) {
                    $need_op_record = 1;
                }
            } catch (Exception $ex) {
                // dd([$emchk1, $em1]);
            }
            // We have an email record match so we should NOT rely on firstName/lastName matching at all
            $pchk = null;
        } elseif ($emchk2->isNotEmpty() && ! empty($emchk2[0]) && ! empty($em2) && $em2 != '' && $em2 != ' ') {
            $emchk2 = $emchk2[0];
            // email2 was found in the database
            // $p  = Person::where(['personID' => $emchk2->personID])->get();
            // $p  = $p[0];
            $op = OrgPerson::where([
                ['personID', $emchk2->personID],
                ['orgID', $currentPerson->defaultOrgID],
            ])->get()->first();
            // $this->timeMem('16 get org person 2202');
            if (empty($op)) {
                $need_op_record = 1;
            }
            // We have an email record match so we should NOT rely on firstName/lastName matching at all
            $pchk = null;
        } elseif ($pchk->isNotEmpty()) {
            // Everything else was null but firstName & lastName matches someone
            $p = $pchk[0];
            $update_existing_record = 1;

            // Should check if there are multiple firstName/lastName matches and then decide what, if anything,
            // can be done to pick the right one...
            if (! empty($p->personID)) {
                $op = OrgPerson::where([
                    ['personID', $p->personID],
                    ['orgID', $currentPerson->defaultOrgID],
                ])->get()->first();
                // $this->timeMem('17 get org person 2218');
                if (empty($op)) {
                    $need_op_record = 1;
                }
            }
        }
        if ($update_existing_record && ! empty($p)) {
            $ary = [];
            if (strlen($prefix) > 0) {
                $ary['prefix'] = $prefix;
            }
            $ary['firstName'] = $first;
            try {
                if (empty($p->prefName)) {
                    $ary['prefName'] = $first;
                }
                if (strlen($midName) > 0) {
                    $ary['midName'] = $midName;
                }
                $ary['lastName'] = $last;
                if (strlen($suffix) > 0) {
                    $ary['suffix'] = $suffix;
                }
                if (empty($p->title) || $pchk !== null) {
                    $ary['title'] = $title;
                }
                if (empty($p->compName) || $pchk !== null) {
                    $ary['compName'] = $compName;
                }
                if (empty($p->affiliation)) {
                    $ary['affiliation'] = $currentPerson->affiliation;
                }
                if (empty($p->experience)) {
                    $ary['experience'] = $currentPerson->experience;
                }


                // One day: think about how to auto-populate indName field using compName

                $ary['updaterID'] = $currentPerson->personID;
                $ary['defaultOrgID'] = $currentPerson->defaultOrgID;
                DB::table('person')->where('personID', $p->personID)->update($ary);
                $has_update = true;
                // $this->timeMem('18 get org person 2257');
            } catch (Exception $ex) {
                // dd($p);
            }
        }

        $memClass = trim(ucwords($row['chapter_member_class']));
        $pmiRenew = trim(ucwords($row['pmiauto_renew_status']));
        $chapRenew = trim(ucwords($row['chapter_auto_renew_status']));

        if ($need_op_record) {
            // A new OP record must be created because EITHER:
            // 1. the member is completely new to the system or
            // 2. the member is in the system but under another chapter/orgID
            $newOP = new OrgPerson;
            //$newOP->orgID = $p->defaultOrgID;
            $newOP->orgID = $this->currentPerson->defaultOrgID;
            $newOP->personID = $p->personID;
            $newOP->OrgStat1 = $pmi_id;

            if (strlen($memClass) > 0) {
                $newOP->OrgStat2 = $memClass;
            }
            // Because OrgStat3 & OrgStat4 data has 'Yes' or blanks as values
            if (strlen($pmiRenew) > 0) {
                if ($pmiRenew != 'Yes') {
                    $newOP->OrgStat3 = 'No';
                } else {
                    $newOP->OrgStat3 = $pmiRenew;
                }
            }

            if (strlen($chapRenew) > 0) {
                if ($chapRenew != 'Yes') {
                    $newOP->OrgStat4 = 'No';
                } else {
                    $newOP->OrgStat4 = $chapRenew;
                }
            }
            if (! empty($row['pmi_join_date']) && isDate($row['pmi_join_date'])) {
                $newOP->RelDate1 = Carbon::createFromFormat('d/m/Y', $row['pmi_join_date'])->toDateTimeString();
            }
            if (! empty($row['chapter_join_date']) && isDate($row['chapter_join_date'])) {
                $newOP->RelDate2 = Carbon::createFromFormat('d/m/Y', $row['chapter_join_date'])->toDateTimeString();
            }
            if (! empty($row['pmi_expiration']) && isDate($row['pmi_expiration'])) {
                $newOP->RelDate3 = Carbon::createFromFormat('d/m/Y', $row['pmi_expiration'])->toDateTimeString();
            }
            if (! empty($row['chapter_expiration']) && isDate($row['chapter_expiration'])) {
                $newOP->RelDate4 = Carbon::createFromFormat('d/m/Y', $row['chapter_expiration'])->toDateTimeString();
            }
            $newOP->creatorID = $currentPerson->personID;
            $newOP->save();

            // $this->timeMem('19 new po update 2312');
            if ($p->defaultOrgPersonID === null) {
                DB::table('person')->where('personID', $p->personID)->update(['defaultOrgPersonID' => $newOP->id]);
                $has_update = true;
                // $this->timeMem('20 person update 2315');
                // $p->defaultOrgPersonID = $newOP->id;
                // $p->save();
            }
        } else {
            // We'll update some fields on the off chance they weren't properly filled in a previous creation
            // $op = $op->toArray();
            if (! empty($op->toArray())) {
                $newOP = $op;
                $ary = [];
                if ($newOP->OrgStat1 === null) {
                    $ary['OrgStat1'] = $pmi_id;
                }

                if (strlen($pmiRenew) > 0) {
                    if ($pmiRenew != 'Yes') {
                        $ary['OrgStat3'] = 'No';
                    } else {
                        $ary['OrgStat3'] = $pmiRenew;
                    }
                }

                if (strlen($chapRenew) > 0) {
                    if ($chapRenew != 'Yes') {
                        $ary['OrgStat4'] = 'No';
                    } else {
                        $ary['OrgStat4'] = $chapRenew;
                    }
                }
                $reld_update = [];
                $rl1 = [];
                $rl2 = [];
                if (! empty($row['pmi_join_date']) && isDate($row['pmi_join_date'])) {
                    if (empty($newOP->RelDate1)) {
                        $ary['RelDate1'] = Carbon::createFromFormat('d/m/Y', $row['pmi_join_date'])->toDateTimeString();
                    } else {
                        $existing_relDate1 = Carbon::createFromFormat('Y-m-d H:i:s', $newOP->RelDate1);
                        $existing_relDate1 = $existing_relDate1->format('d/m/Y');
                        if ($row['pmi_join_date'] != $existing_relDate1) {
                            // Added to have join date(s) corrected if what PMI provided is an earlier date than what is in DB
                            $test_date = Carbon::createFromFormat('d/m/Y', $row['pmi_join_date'])->toDateTimeString();
                            $msg = '';
                            if($newOP->RelDate1->gt($test_date)){
                                $ary['RelDate1'] = Carbon::createFromFormat('d/m/Y', $row['pmi_join_date'])->toDateTimeString();
                                $msg = 'Changed pmi_join_date in DB.';
                            }
                            $rl1 = [
                                'pmi_id'       => $pmi_id,
                                'reldate1_new' => $row['pmi_join_date'],
                                'reldate1_old' => $existing_relDate1,
                                'msg' => $msg,
                            ];
                        }
                    }
                }
                if (! empty($row['chapter_join_date']) && isDate($row['chapter_join_date'])) {
                    if (empty($newOP->RelDate2)) {
                        $ary['RelDate2'] = Carbon::createFromFormat('d/m/Y', $row['chapter_join_date'])->toDateTimeString();
                    } else {
                        $existing_relDate2 = Carbon::createFromFormat('Y-m-d H:i:s', $newOP->RelDate2);
                        $existing_relDate2 = $existing_relDate2->format('d/m/Y');
                        if ($row['chapter_join_date'] != $existing_relDate2) {
                            $reld_update[$pmi_id]['reldate2_new'] = $row['chapter_join_date'];
                            $reld_update[$pmi_id]['reldate2_old'] = $existing_relDate2;

                            // Added to have join date(s) corrected if what PMI provided is an earlier date than what is in DB
                            $test_date = Carbon::createFromFormat('d/m/Y', $row['chapter_join_date'])->toDateTimeString();
                            $msg = '';
                            if($newOP->RelDate1->gt($test_date)){
                                $ary['RelDate2'] = Carbon::createFromFormat('d/m/Y', $row['chapter_join_date'])->toDateTimeString();
                                $msg = 'Changed chapter_join_date in DB.';
                            }
                            $rl2 = [
                                'pmi_id'       => $pmi_id,
                                'reldate2_new' => $row['chapter_join_date'],
                                'reldate2_old' => $existing_relDate2,
                                'msg' => $msg,
                            ];
                        }
                    }
                }
                $reld_update = array_merge($rl1, $rl2);
                if (! empty($reld_update)) {
                    if (! empty($this->import_detail->other)) {
                        $json = json_decode($this->import_detail->other);
                        $json[] = $reld_update;
                        $this->import_detail->other = json_encode($json);
                    } else {
                        $this->import_detail->other = json_encode([$reld_update]);
                    }
                    $this->import_detail->save();
                }

                if (! empty($row['pmi_expiration']) && isDate($row['pmi_expiration'])) {
                    $ary['RelDate3'] = Carbon::createFromFormat('d/m/Y', $row['pmi_expiration'])->toDateTimeString();
                }
                if (! empty($row['chapter_expiration']) && isDate($row['chapter_expiration'])) {
                    $ary['RelDate4'] = Carbon::createFromFormat('d/m/Y', $row['chapter_expiration'])->toDateTimeString();
                }
                $ary['updaterID'] = $currentPerson->personID;
                DB::table('org-person')->where('id', $newOP->id)->update($ary);
                $has_update = true;
                // $this->timeMem('21 update org person 2358');
                // $newOP->save();
            }
        }

        // Add the person-specific records as needed
        // This logic will only work reliably for chapters in the US given the zip as 5 or 9 digits1F
        if (! empty($p)) {
            $pa = trim(ucwords($row['preferred_address']));
            $addr = Address::where(['addr1' => $pa, 'personId' => $p->personID])->limit(1)->get();
            // $this->timeMem('22 get address 2367');
            if ($addr->isEmpty() && $pa !== null && $pa != '' && $pa != ' ') {
                $z = trim($row['zip']);
                if (strlen($z) == 4) {
                    $z = '0'.$z;
                } elseif (strlen($z) == 8) {
                    $r2 = substr($z, -4, 4);
                    $l2 = substr($z, 0, 4);
                    $z = '0'.$l2.'-'.$r2;
                } elseif (strlen($z) == 9) {
                    $r2 = substr($z, -4, 4);
                    $l2 = substr($z, 0, 5);
                    $z = $l2.'-'.$r2;
                }
                // $addr->zip = $z;

                // // Need a smarter way to determine country code
                $cntry = trim(ucwords($row['country']));
                $cntry_id = 228;
                if ($cntry == 'United States') {
                    $addr->cntryID = 228;
                    $cntry_id = 228;
                } elseif ($cntry == 'Canada') {
                    $addr->cntryID = 36;
                    $cntry_id = 36;
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

            if (! empty($num)) {
                // $phone = Phone::whereIn('phoneNumber', $num)->get();
                $phone = DB::table('person-phone')->whereIn('phoneNumber', $num)->get();
                // $this->timeMem('23 get phone 2419');
                if ($phone->isNotEmpty()) {
                    foreach ($phone as $key => $value) {
                        if ($value->phoneID == $p->personID) {
                            $ary = [
                                'debugNote' => "ugh!  Was: $value->personID; Should be: $p->personID",
                                'personID'  => $p->personID,
                            ];
                            DB::table('person-phone')->where('id', $value->phoneID)->update($ary);
                        }
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
        if ($has_insert) {
            $import_detail->increment('inserted');
        }
        if ($has_update && ! $has_insert) {
            $import_detail->increment('updated');
        }

        if ($has_update == false && $has_insert == false) {
            $import_detail->increment('failed');
            if (! empty($import_detail->failed_records)) {
                $json = json_decode($import_detail->failed_records);
                $data = [
                    'reason'                => trans('messages.notifications.member_import.nothing_to_update'),
                    'pmi_id'                => $pmi_id,
                    'first_name'            => $first,
                    'last_name'             => $last,
                    'primary_email'         => $em1,
                    'alternate_email_email' => $em2, ];
                $json[] = $data;
                $import_detail->failed_records = json_encode($json);
            } else {
                $import_detail->failed_records = json_encode([$row]);
            }
            $import_detail->save();
        }
        $this->bulkInsertAll();
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
            'creatorID'    => $this->currentPerson->personID,
        ];
    }

    public function insertAddress($personID, $addresstype, $addr1, $city, $state, $zip, $country)
    {
        $this->address_master[] = [
            'personID'  => $personID,
            'addrTYPE'  => $addresstype,
            'addr1'     => $addr1,
            'city'      => $city,
            'state'     => $state,
            'zip'       => $zip,
            'cntryID'   => $country,
            'creatorID' => $this->currentPerson->personID,
            'updaterID' => $this->currentPerson->personID,
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
            'creatorID'   => $this->currentPerson->personID,
            'updaterID'   => $this->currentPerson->personID,
        ];
    }

    /**
     * create bulk insert array for email
     * @param  int  $personID
     * @param  string  $email
     * @param  int $primary
     * @return [type]
     */
    public function insertEmail($personID, $email, $primary = 0)
    {
        //it has creatorID and UpdaterID user auth user id
        $this->email_master[] = [
            'personID'  => $personID,
            'emailADDR' => $email,
            'isPrimary' => $primary,
            'creatorID' => $this->currentPerson->personID,
            'updaterID' => $this->currentPerson->personID,
        ];
    }

    public function bulkInsertAll()
    {
        if (! empty($this->email_master)) {
            try {
                Email::insertIgnore($this->email_master);
            } catch (Exception $ex) {
                //do nothing
            }
            // $this->timeMem('24 inset bulk email ' . count($this->email_master));
        }

        if (! empty($this->phone_master)) {
            try {
                Phone::insertIgnore($this->phone_master);
            } catch (Exception $ex) {
                //do nothing
            }
            // $this->timeMem('25 insert bulk phone ' . count($this->phone_master));
        }

        if (! empty($this->address_master)) {
            try {
                Address::insertIgnore($this->address_master);
            } catch (Exception $ex) {
                //do nothing
            }
            // $this->timeMem('26 inset bulk addres ' . count($this->address_master));
        }

        if (! empty($this->person_staging_master)) {
            PersonStaging::insertIgnore($this->person_staging_master);
            // $this->timeMem('27 inset bulk personstaggin ' . count($this->person_staging_master));
        }

        $this->email_master = [];
        $this->phone_master = [];
        $this->address_master = [];
        $this->person_staging_master = [];
        // $this->person_staging_master = array();
    }
}

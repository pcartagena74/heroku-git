<?php
/**
 * Comment: created for use with Google Captcha front-end interface
 *          per: https://tuts.codingo.me/google-recaptcha-in-laravel-application
 * Created: 3/16/2017
 */

namespace App\Traits;

trait MemberImportTrait
{

    public function timeMem($msg = null)
    {
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
    public function storeDataDB($row, $currentPerson)
    {

        $count = 0;
        $this->timeMem('Loop start');
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
        $op     = OrgPerson::where([
            ['OrgStat1', $pmi_id],
            ['orgID', $currentPerson->defaultOrgID],
        ])->get();

        $any_op = OrgPerson::where('OrgStat1', $pmi_id)->get();

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

        $em1 = trim(strtolower($row['primary_email']));

        if (strlen($em1) > 0 && strpos($em1, '@')) {
            $emchk1 = Email::whereRaw('lower(emailADDR) = ?', [$em1])->withTrashed()->limit(1)->get();
        } else {
            // The email address in $em1 was not valid so null it all out
            $em1    = null;
            $emchk1 = new Collection();
        }
        if ($emchk1->isNotEmpty()) {
            $chk1 = $emchk1[0]->emailADDR;
        }

        $em2 = trim(strtolower($row['alternate_email']));
        if (strlen($em2) > 0 && strpos($em2, '@')) {
            $emchk2 = Email::whereRaw('lower(emailADDR) = ?', [$em2])->withTrashed()->limit(1)->get();
        } else {
            // The email address in $em2 was not valid so null it all out
            $em2    = null;
            $emchk2 = new Collection();
        }
        if ($emchk2->isNotEmpty()) {
            $chk2 = $emchk2[0]->emailADDR;
        }

        $pchk = Person::where([
            ['firstName', '=', $first],
            ['lastName', '=', $last],
        ])->limit(1)->get();
        $this->timeMem('before if');
        // dd([$op, $any_op, $emchk1, $emchk2, $pchk]);
        if ($op->isEmpty() && $any_op->isEmpty() && $emchk1->isEmpty() && $emchk2->isEmpty() && $pchk->isEmpty()) {

            // PMI ID, first & last names, and emails are not found so person is likely completely new; create all records

            $need_op_record = 1;
            $p              = '';
            $u              = '';
            // $p              = new Person;
            // $u              = new User;
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
            // $p->prefix       = $prefix;
            // $p->firstName    = $first;
            // $p->prefName     = $first;
            // $p->midName      = $midName;
            // $p->lastName     = $last;
            // $p->suffix       = $suffix;
            // $p->title        = $title;
            // $p->compName     = $compName;
            // $p->creatorID    = auth()->user()->id;
            // $p->defaultOrgID = $currentPerson->defaultOrgID;
            // if ($p->affiliation === null) {
            //     $p->affiliation = $currentPerson->affiliation;
            // }
            $update_existing_record = 0;

            // If email1 is not null or blank, use it as primary to login, etc.
            if ($em1 !== null && $em1 != "" && $em1 != " ") {
                $p_array['login'] = $em1;
                $p                = Person::create($p_array);
                // $p->login = $em1;
                // $p->save();
                $u_array = [
                    'id'    => $p->personID,
                    'login' => $em1,
                    'name'  => $em1,
                    'email' => $em1,
                ];
                $u = User::create($u_array);
                // $u->id    = $p->personID;
                // $u->login = $em1;
                // $u->name  = $em1;
                // $u->email = $em1;
                // $u->save();
                $this->timeMem('162 p and u save');
                $e            = new Email;
                $e->personID  = $p->personID;
                $e->emailADDR = $em1;
                $e->isPrimary = 1;
                $e->creatorID = auth()->user()->id;
                $e->updaterID = auth()->user()->id;
                $e->save();

                // Otherwise, try with email #2
            } elseif ($em2 !== null && $em2 != '' && $em2 != ' ' && $p->login === null) {
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
                    $this->timeMem('email save');
                } catch (\Exception $exception) {
                    // There was an error with saving the email -- likely an integrity constraint.
                }
            } elseif ($pchk !== null) {
                // I don't think this code can actually run.
                // The $pchk check in the outer loop is what this should have been.

                // Emails didn't match for some reason but found a first/last name match
                // Recheck to see if there's just 1 match
                // $pchk_count = Person::where([
                //     ['firstName', '=', $first],
                //     ['lastName', '=', $last],
                // ])->get();
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
                break;
            }

            // If email 1 exists and was used as primary but email 2 was also provided and unique, add it.
            if ($em1 !== null && $em2 !== null && $em2 != $em1 && $em2 != "" && $em2 != " " && $em2 != $chk2) {
                $e            = new Email;
                $e->personID  = $p->personID;
                $e->emailADDR = $em2;
                $e->creatorID = auth()->user()->id;
                $e->updaterID = auth()->user()->id;
                try {
                    $e->save();
                    $this->timeMem('email save 2');
                } catch (\Exception $exception) {
                    // There was an error with saving the email -- likely an integrity constraint.
                }
            } elseif ($em2 !== null && $em2 == strtolower($chk2)) {
                if ($emchk2->personID != $p->personID) {
                    $emchk2->debugNote = "ugh!  Was: $emchk2->personID; Should be: $p->personID";
                    $emchk2->personID  = $p->personID;
                    $emchk2->save();
                    $this->timeMem('email update');
                }

            }
            unset($u);
            unset($emchk2);
            unset($e);
            $this->timeMem('after if unset');
        } elseif ($op->isNotEmpty() || $any_op->isNotEmpty()) {
            // There was an org-person record (found by $OrgStat1 == PMI ID) for this chapter/orgID
            if ($op->isNotEmpty()) {
                // For modularity, updating the $op record will happen below as there are no dependencies
                $p = Person::where(['personID' => $op[0]->personID])->get();
                $p = $p[0];
            } else {
                $need_op_record = 1;
                $p              = Person::where(['personID' => $any_op[0]->personID])->get();
                $p              = $p[0];
            }

            // We have an $org-person record so we should NOT rely on firstName/lastName matching at all
            $pchk = null;

            // Because we should have found a person record, determine if we should create and associate email records
            if ($em1 !== null && $em1 != "" && $em1 != " " && $em1 != strtolower($chk1) && $em1 != strtolower($chk2)) {
                $e            = new Email;
                $e->personID  = $p->personID;
                $e->emailADDR = $em1;
                // We do not need to override existing primary settings
                // $e->isPrimary = 1;
                $e->creatorID = auth()->user()->id;
                $e->updaterID = auth()->user()->id;
                $e->save();
            } elseif ($em1 !== null && $em1 == strtolower($chk1)) {
                if ($emchk1[0]->personID != $p->personID) {
                    $emchk1[0]->debugNote = "ugh!  Was: $emchk1[0]->personID; Should be: $p->personID";
                    $emchk1[0]->personID  = $p->personID;
                    $emchk1->save();
                }
            }
            if ($em2 !== null && $em2 != "" && $em2 != " " && $em2 != strtolower($chk1) && $em2 != strtolower($chk2) && $em2 != $em1) {
                $e            = new Email;
                $e->personID  = $p->personID;
                $e->emailADDR = $em2;
                // We do not need to override existing primary settings
                // $e->isPrimary = 1;
                $e->creatorID = auth()->user()->id;
                $e->updaterID = auth()->user()->id;
                $e->save();
            } elseif ($em2 !== null && $em2 == strtolower($chk2)) {
                if ($emchk2->personID != $p->personID) {
                    $emchk2->debugNote = "ugh!  Was: $emchk2->personID; Should be: $p->personID";
                    $emchk2->personID  = $p->personID;
                    $emchk2->save();
                }
            }
        } elseif ($emchk1->isNotEmpty() && $em1->isNotEmpty() && $em1 != '' && $em1 != ' ') {
            // email1 was found in the database, but either no PMI ID match in DB, possibly due to a different/incorrect entry
            $p  = Person::where(['personID' => $emchk1->personID])->get();
            $p  = $p[0];
            $op = OrgPerson::where([
                ['personID', $p->personID],
                ['orgID', $currentPerson->defaultOrgID],
            ])->get();
            if ($op->isEmpty()) {$need_op_record = 1;}
            // We have an email record match so we should NOT rely on firstName/lastName matching at all
            $pchk = null;
        } elseif ($emchk2->isNotEmpty() && $em2->isNotEmpty() && $em2 != '' && $em2 != ' ') {
            // email2 was found in the database
            $p  = Person::where(['personID' => $emchk2->personID])->get();
            $p  = $p[0];
            $op = OrgPerson::where([
                ['personID', $p->personID],
                ['orgID', $currentPerson->defaultOrgID],
            ])->get();
            if ($op->isEmpty()) {$need_op_record = 1;}
            // We have an email record match so we should NOT rely on firstName/lastName matching at all
            $pchk = null;
        } elseif ($pchk->isNotEmpty()) {
            // Everything else was null but firstName & lastName matches someone
            $p                      = $pchk;
            $update_existing_record = 1;

            // Should check if there are multiple firstName/lastName matches and then decide what, if anything,
            // can be done to pick the right one...
            $op = OrgPerson::where([
                ['personID', $p->personID],
                ['orgID', $currentPerson->defaultOrgID],
            ])->get();

            if ($op->isEmpty()) {
                $need_op_record = 1;
            }
        }

        if ($update_existing_record) {
            if (strlen($prefix) > 0) {
                $p->prefix = $prefix;
            }
            $p->firstName = $first;
            if ($p->prefName === null) {
                $p->prefName = $first;
            }
            if (strlen($midName) > 0) {
                $p->midName = $midName;
            }
            $p->lastName = $last;
            if (strlen($suffix) > 0) {
                $p->suffix = $suffix;
            }
            if ($p->title === null || $pchk !== null) {
                $p->title = $title;
            }
            if ($p->compName === null || $pchk !== null) {
                $p->compName = $compName;
            }
            if ($p->affiliation === null) {
                $p->affiliation = $currentPerson->affiliation;
            }

            // One day: think about how to auto-populate indName field using compName

            $p->updaterID    = auth()->user()->id;
            $p->defaultOrgID = $currentPerson->defaultOrgID;
            $p->save();
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

            if ($p->defaultOrgPersonID === null) {
                $p->defaultOrgPersonID = $newOP->id;
                $p->save();
            }
        } else {
            // We'll update some fields on the off chance they weren't properly filled in a previous creation
            $newOP = $op[0];
            if ($newOP->OrgStat1 === null) {
                $newOP->OrgStat1 = $pmi_id;
            }

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
            $newOP->updaterID = auth()->user()->id;
            $newOP->save();
        }

        // Add the person-specific records as needed

        $pa   = trim(ucwords($row['preferred_address']));
        $addr = Address::where('addr1', '=', $pa)->limit(1)->get();
        if ($addr->isEmpty() && $pa !== null && $pa != "" && $pa != " ") {
            $addr           = new Address;
            $addr->personID = $p->personID;
            $addr->addrTYPE = trim(ucwords($row['preferred_address_type']));
            $addr->addr1    = trim(ucwords($row['preferred_address']));
            $addr->city     = trim(ucwords($row['city']));
            $addr->state    = trim(ucwords($row['state']));
            $z              = trim($row['zip']);
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
            $addr->zip = $z;

            // Need a smarter way to determine country code
            $cntry = trim(ucwords($row['country']));
            if ($cntry == 'United States') {
                $addr->cntryID = 228;
            } elseif ($cntry == 'Canada') {
                $addr->cntryID = 36;
            }
            $addr->creatorID = auth()->user()->id;
            $addr->updaterID = auth()->user()->id;
            $addr->save();
        }

        $hp = trim($row['home_phone']);
        if (strlen($hp) > 7) {
            $fone = Phone::where([
                ['phoneNumber', '=', $hp],
            ])->withTrashed()->limit(1)->get();
        } else {
            $fone = new Collection();
        }

        if ($hp !== null && strlen($hp) > 7 && $fone->isEmpty()) {
            $fone              = new Phone;
            $fone->personID    = $p->personID;
            $fone->phoneNumber = $hp;
            $fone->phoneType   = 'Home';
            $fone->creatorID   = auth()->user()->id;
            $fone->updaterID   = auth()->user()->id;
            $fone->save();
        } elseif (strlen($hp) > 7 && $fone->isNotEmpty()) {
            if ($fone[0]->personID != $p->personID) {
                $fone[0]->debugNote = "ugh!  Was: $fone->personID; Should be: $p->personID";
                $fone[0]->personID  = $p->personID;
                $fone->save();
            }
        }

        $wp = trim($row['work_phone']);
        if (strlen($wp) > 7) {
            $fone = Phone::where([
                ['phoneNumber', '=', $wp],
            ])->withTrashed()->limit(1)->get();
        } else {
            $fone = new Collection;
        }

        if ($wp !== null && strlen($wp) > 7 && $fone->isEmpty()) {
            $fone              = new Phone;
            $fone->personID    = $p->personID;
            $fone->phoneNumber = $wp;
            $fone->phoneType   = 'Work';
            $fone->creatorID   = auth()->user()->id;
            $fone->updaterID   = auth()->user()->id;
            $fone->save();
        } elseif (strlen($wp) > 7 && $fone->isNotEmpty()) {
            if ($fone[0]->personID != $p->personID) {
                $fone[0]->debugNote = "ugh!  Was: $fone->personID; Should be: $p->personID";
                $fone[0]->personID  = $p->personID;
                $fone->save();
            }
        }

        $mp = trim($row['mobile_phone']);
        if (strlen($mp) > 7) {
            $fone = Phone::where([
                ['phoneNumber', '=', $mp],
            ])->withTrashed()->limit(1)->get();
        } else {
            $fone = new Collection();
        }

        if ($mp !== null && strlen($wp) > 7 && $fone->isEmpty()) {
            $fone              = new Phone;
            $fone->personID    = $p->personID;
            $fone->phoneNumber = $mp;
            $fone->phoneType   = 'Mobile';
            $fone->creatorID   = auth()->user()->id;
            $fone->updaterID   = auth()->user()->id;
            $fone->save();
        } elseif (strlen($mp) > 7 && $fone->isNotEmpty()) {
            if ($fone[0]->personID != $p->personID) {
                $fone[0]->debugNote = "ugh!  Was: $fone->personID; Should be: $p->personID";
                $fone[0]->personID  = $p->personID;
                $fone->save();
            }
        }

        $ps               = new PersonStaging;
        $ps->personID     = $p->personID;
        $ps->prefix       = $prefix;
        $ps->firstName    = $first;
        $ps->midName      = $midName;
        $ps->lastName     = $last;
        $ps->suffix       = $suffix;
        $ps->login        = $p->login;
        $ps->title        = $title;
        $ps->compName     = $compName;
        $ps->defaultOrgID = $currentPerson->defaultOrgID;
        $ps->creatorID    = auth()->user()->id;
        $ps->save();

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

        //
        // $chk1           = null;
        // $chk2           = null;
        // $p              = null;
        // $u              = null;
        // $f              = null;
        // $l              = null;
        // $e              = null;
        // $need_op_record = null;
        // $pchk           = null;
        // $op             = null;
        // $addr           = null;
        // $fone           = null;
        // $pmi_id         = null;
        // $fone           = null;
        // $newOP          = null;
        // $emchk1         = null;
        // $emchk2         = null;
        // $ps             = null;
        // $row            = null;
        // $this->timeMem();
        // gc_collect_cycles();

        //herer
    }

    // unset($rows);
    // $gc = gc_collect_cycles();
    $this->timeMem('Last message');
    // dd(get_defined_vars());

    // request()->session()->flash('alert-success', trans('messages.admin.upload.loaded',
    // ['what' => trans('messages.admin.upload.mbrdata'), 'count' => $count]));

}
}

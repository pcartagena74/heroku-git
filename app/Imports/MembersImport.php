<?php

namespace App\Imports;

use App\Models\Address;
use App\Models\Email;
use App\Models\OrgPerson;
use App\Models\Person;
use App\Models\Phone;
use App\Models\User;
use App\Traits\ExcelMemberImportTrait;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class MembersImport implements ShouldQueue, SkipsOnFailure, ToCollection, WithChunkReading, WithHeadingRow, WithValidation
{
    // WithBatchInserts only works with toModel concern
    use ExcelMemberImportTrait, Importable, SkipsFailures;

    public $starttime;

    public $phone_master;

    public $email_master;

    public $address_master;

    public $person_staging_master;

    public $row_count = 0;

    public $currentPerson;

    protected $tries = 3;

    protected $timeout = 160;

    public function __construct($currentPerson, $import_detail)
    {
        $this->currentPerson = $currentPerson;
        $this->import_detail = $import_detail;
        $this->em1 = null;
        // requestBin(['in'=>'constructor member import']);
    }

    /**
     * @param  Collection  $rows
     *                            Member data consists of demographic data -> person / orgperson / user as well as email, address, and phone
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function collection(Collection $rows)
    {
        $user = User::where('id', $this->currentPerson->personID)->get()->first();
        \App::setLocale($user->locale);
        $count = 0;
        foreach ($rows as $row) {
            $this->import_detail->refresh();
            $this->import_detail->increment('total');
            if (! empty($row['pmi_id']) && (! empty($row['primary_email']) || ! empty($row['alternate_email']))) {
                // requestBin($row->toArray());
                $count++;
                $this->storeImportDataDB($row->toArray(), $this->currentPerson, $this->import_detail);
            } else {
                $this->import_detail->increment('failed');
                $reason = ['reason' => trans('messages.errors.import_validation'),
                    'pmi_id' => $row['pmi_id'],
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'primary_email' => $row['primary_email'],
                    'alternate_email' => $row['alternate_email'],
                ];
                if (! empty($this->import_detail->failed_records)) {
                    $json = json_decode($this->import_detail->failed_records);
                    $json[] = $reason;
                    $this->import_detail->failed_records = json_encode($json);
                } else {
                    $this->import_detail->failed_records = json_encode([$reason]);
                }
                $this->import_detail->save();
            }
        }
        $this->row_count += $count;

        /*
        // old code not to use
        $currentPerson = Person::find(auth()->user()->id);
        $count = 0;
        foreach ($rows as $row) {
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

            // columns in the MemberDetail sheet are fixed; check directly and then add if not found...
            // foreach $row, search on $row->pmi_id, then $row->primary_email, then $row->alternate_email
            // if found, get $person, $org-person, $email, $address, $phone records and update, else create

            $pmi_id = trim($row['pmi_id']);
            $op = OrgPerson::where([
                ['OrgStat1', $pmi_id],
                ['orgID', $currentPerson->defaultOrgID],
            ])->first();

            $any_op = OrgPerson::where('OrgStat1', $pmi_id)->first();

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
            $this->em1 = $em1;

            if (strlen($em1) > 0 && strpos($em1, '@')) {
                $emchk1 = Email::whereRaw('lower(emailADDR) = ?', [$em1])->withTrashed()->first();
            } else {
                // The email address in $em1 was not valid so null it all out
                $em1 = null;
                $this->em1 = $em1;
                $emchk1 = null;
            }
            if ($emchk1 !== null) {
                $chk1 = $emchk1->emailADDR;
            }

            $em2 = trim(strtolower($row['alternate_email']));
            if (strlen($em2) > 0 && strpos($em2, '@')) {
                $emchk2 = Email::whereRaw('lower(emailADDR) = ?', [$em2])->withTrashed()->first();
            } else {
                // The email address in $em2 was not valid so null it all out
                $em2 = null;
                $emchk2 = null;
            }
            if ($emchk2 !== null) {
                $chk2 = $emchk2->emailADDR;
            }

            $pchk = Person::where([
                ['firstName', '=', $first],
                ['lastName', '=', $last],
            ])->first();

            if ($op === null && $any_op === null && $emchk1 === null && $emchk2 === null && $pchk === null) {
                // PMI ID, first & last names, and emails are not found so person is likely completely new; create all records

                $need_op_record = 1;
                $p = new Person;
                $u = new User;
                $p->prefix = $prefix;
                $p->firstName = $first;
                $p->prefName = $first;
                $p->midName = $midName;
                $p->lastName = $last;
                $p->suffix = $suffix;
                $p->title = $title;
                $p->compName = $compName;
                $p->creatorID = auth()->user()->id;
                $p->defaultOrgID = $currentPerson->defaultOrgID;
                if ($p->affiliation === null) {
                    $p->affiliation = $currentPerson->affiliation;
                }
                $update_existing_record = 0;

                // If email1 is not null or blank, use it as primary to login, etc.
                if ($em1 !== null && $em1 != '' && $em1 != ' ' && $p->login === null) {
                    // $p->login = $em1;
                    // $p->save();
                    // $u->id    = $p->personID;
                    // $u->login = $em1;
                    // $u->name  = $em1;
                    // $u->email = $em1;
                    // $u->save();

                    // $e            = new Email;
                    // $e->personID  = $p->personID;
                    // $e->emailADDR = $em1;
                    // $e->isPrimary = 1;
                    // $e->creatorID = auth()->user()->id;
                    // $e->updaterID = auth()->user()->id;
                    // $e->save();

                    // Otherwise, try with email #2
                } elseif ($em2 !== null && $em2 != '' && $em2 != ' ' && $p->login === null) {
                    // $p->login = $em2;
                    // $p->save();
                    // $u->id    = $p->personID;
                    // $u->login = $em2;
                    // $u->name  = $em2;
                    // $u->email = $em2;
                    // $u->save();

                    // $e            = new Email;
                    // $e->personID  = $p->personID;
                    // $e->emailADDR = $em2;
                    // $e->isPrimary = 1;
                    // $e->creatorID = auth()->user()->id;
                    // $e->updaterID = auth()->user()->id;
                    try {
                        // $e->save();
                    } catch (\Exception $exception) {
                        // There was an error with saving the email -- likely an integrity constraint.
                    }
                } elseif ($pchk !== null) {
                    // I don't think this code can actually run.
                    // The $pchk check in the outer loop is what this should have been.

                    // Emails didn't match for some reason but found a first/last name match
                    // Recheck to see if there's just 1 match
                    $pchk_count = Person::where([
                        ['firstName', '=', $first],
                        ['lastName', '=', $last],
                    ])->get();
                    if (count($pchk_count) == 1) {
                        $p = $pchk;
                    } else {
                        // Would need a way to pick the right one if there's more than 1
                        // For now, just taking the first one
                        $p = $pchk;
                    }
                } else {
                    // This is a last resort when there are no email addresses associated with the record
                    // Better to abandon; avoid $p->save();
                    // Technically, should not ever get here because we check ahead of time.
                    break;
                }

                // If email 1 exists and was used as primary but email 2 was also provided and unique, add it.
                if ($em1 !== null && $em2 !== null && $em2 != $em1 && $em2 != '' && $em2 != ' ' && $em2 != $chk2) {
                    // $e            = new Email;
                    // $e->personID  = $p->personID;
                    // $e->emailADDR = $em2;
                    // $e->creatorID = auth()->user()->id;
                    // $e->updaterID = auth()->user()->id;
                    try {
                        // $e->save();
                    } catch (\Exception $exception) {
                        // There was an error with saving the email -- likely an integrity constraint.
                    }
                } elseif ($em2 !== null && $em2 == strtolower($chk2)) {
                    // if ($emchk2->personID != $p->personID) {
                    //     $emchk2->debugNote = "ugh!  Was: $emchk2->personID; Should be: $p->personID";
                    //     $emchk2->personID  = $p->personID;
                    //     $emchk2->save();
                    // }
                }
            } elseif ($op !== null || $any_op !== null) {
                // There was an org-person record (found by $OrgStat1 == PMI ID) for this chapter/orgID
                if ($op !== null) {
                    // For modularity, updating the $op record will happen below as there are no dependencies
                    $p = Person::find($op->personID);
                } else {
                    $need_op_record = 1;
                    $p = Person::find($any_op->personID);
                }

                // We have an $org-person record so we should NOT rely on firstName/lastName matching at all
                $pchk = null;

                // Because we should have found a person record, determine if we should create and associate email records
                if ($em1 !== null && $em1 != '' && $em1 != ' ' && $em1 != strtolower($chk1) && $em1 != strtolower($chk2)) {
                    // $e            = new Email;
                    // $e->personID  = $p->personID;
                    // $e->emailADDR = $em1;
                    // // We do not need to override existing primary settings
                    // // $e->isPrimary = 1;
                    // $e->creatorID = auth()->user()->id;
                    // $e->updaterID = auth()->user()->id;
                    // $e->save();
                } elseif ($em1 !== null && $em1 == strtolower($chk1)) {
                    // if ($emchk1->personID != $p->personID) {
                    //     $emchk1->debugNote = "ugh!  Was: $emchk1->personID; Should be: $p->personID";
                    //     $emchk1->personID  = $p->personID;
                    //     $emchk1->save();
                    // }
                }
                if ($em2 !== null && $em2 != '' && $em2 != ' ' && $em2 != strtolower($chk1) && $em2 != strtolower($chk2) && $em2 != $em1) {
                    // $e            = new Email;
                    // $e->personID  = $p->personID;
                    // $e->emailADDR = $em2;
                    // // We do not need to override existing primary settings
                    // // $e->isPrimary = 1;
                    // $e->creatorID = auth()->user()->id;
                    // $e->updaterID = auth()->user()->id;
                    // $e->save();
                } elseif ($em2 !== null && $em2 == strtolower($chk2)) {
                    // if ($emchk2->personID != $p->personID) {
                    //     $emchk2->debugNote = "ugh!  Was: $emchk2->personID; Should be: $p->personID";
                    //     $emchk2->personID  = $p->personID;
                    //     $emchk2->save();
                    // }
                }
            } elseif ($emchk1 !== null && $em1 !== null && $em1 != '' && $em1 != ' ') {
                // email1 was found in the database, but either no PMI ID match in DB, possibly due to a different/incorrect entry
                $p = Person::find($emchk1->personID);
                $op = OrgPerson::where([
                    ['personID', $p->personID],
                    ['orgID', $currentPerson->defaultOrgID],
                ])->first();
                if ($op === null) {
                    $need_op_record = 1;
                }
                // We have an email record match so we should NOT rely on firstName/lastName matching at all
                $pchk = null;
            } elseif ($emchk2 !== null && $em2 !== null && $em2 != '' && $em2 != ' ') {
                // email2 was found in the database
                $p = Person::find($emchk2->personID);
                $op = OrgPerson::where([
                    ['personID', $p->personID],
                    ['orgID', $currentPerson->defaultOrgID],
                ])->first();
                if ($op === null) {
                    $need_op_record = 1;
                }
                // We have an email record match so we should NOT rely on firstName/lastName matching at all
                $pchk = null;
            } elseif ($pchk !== null) {
                // Everything else was null but firstName & lastName matches someone
                $p = $pchk;
                $update_existing_record = 1;

                // Should check if there are multiple firstName/lastName matches and then decide what, if anything,
                // can be done to pick the right one...
                // $op = OrgPerson::where([
                //     ['personID', $p->personID],
                //     ['orgID', $currentPerson->defaultOrgID],
                // ])->first();

                if ($op === null) {
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

                $p->updaterID = auth()->user()->id;
                $p->defaultOrgID = $currentPerson->defaultOrgID;
                // $p->save();
            }

            $memClass = trim(ucwords($row['chapter_member_class']));
            $pmiRenew = trim(ucwords($row['pmiauto_renew_status']));
            $chapRenew = trim(ucwords($row['chapter_auto_renew_status']));

            if ($need_op_record) {
                // A new OP record must be created because EITHER:
                // 1. the member is completely new to the system or
                // 2. the member is in the system but under another chapter/orgID
                $newOP = new OrgPerson;
                $newOP->orgID = $p->defaultOrgID;
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
            // $newOP->save();

                // if ($p->defaultOrgPersonID === null) {
                //     $p->defaultOrgPersonID = $newOP->id;
                //     $p->save();
                // }
            } else {
                // We'll update some fields on the off chance they weren't properly filled in a previous creation
                $newOP = $op;
                if ($newOP->OrgStat1 === null) {
                    $newOP->OrgStat1 = $pmi_id;
                }

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
                // $newOP->save();
            }

            // Add the person-specific records as needed

            $pa = trim(ucwords($row['preferred_address']));
            $addr = Address::where('addr1', '=', $pa)->first();
            if ($addr === null && $pa !== null && $pa != '' && $pa != ' ') {
                $addr = new Address;
                $addr->personID = $p->personID;
                $addr->addrTYPE = trim(ucwords($row['preferred_address_type']));
                $addr->addr1 = trim(ucwords($row['preferred_address']));
                $addr->city = trim(ucwords($row['city']));
                $addr->state = trim(ucwords($row['state']));
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
                // $addr->save();
            }

            $hp = trim($row['home_phone']);
            if (strlen($hp) > 7) {
                $fone = Phone::where([
                    ['phoneNumber', '=', $hp],
                ])->withTrashed()->first();
            } else {
                $fone = null;
            }

            if ($hp !== null && strlen($hp) > 7 && $fone === null) {
                $fone = new Phone;
                $fone->personID = $p->personID;
                $fone->phoneNumber = $hp;
                $fone->phoneType = 'Home';
                $fone->creatorID = auth()->user()->id;
                $fone->updaterID = auth()->user()->id;
            // $fone->save();
            } elseif (strlen($hp) > 7 && $fone !== null) {
                // if ($fone->personID != $p->personID) {
                //     $fone->debugNote = "ugh!  Was: $fone->personID; Should be: $p->personID";
                //     $fone->personID  = $p->personID;
                //     $fone->save();
                // }
            }

            $wp = trim($row['work_phone']);
            if (strlen($wp) > 7) {
                $fone = Phone::where([
                    ['phoneNumber', '=', $wp],
                ])->withTrashed()->first();
            } else {
                $fone = null;
            }

            if ($wp !== null && strlen($wp) > 7 && $fone === null) {
                $fone = new Phone;
                $fone->personID = $p->personID;
                $fone->phoneNumber = $wp;
                $fone->phoneType = 'Work';
                $fone->creatorID = auth()->user()->id;
                $fone->updaterID = auth()->user()->id;
            // $fone->save();
            } elseif (strlen($wp) > 7 && $fone !== null) {
                if ($fone->personID != $p->personID) {
                    $fone->debugNote = "ugh!  Was: $fone->personID; Should be: $p->personID";
                    $fone->personID = $p->personID;
                    // $fone->save();
                }
            }

            $mp = trim($row['mobile_phone']);
            if (strlen($mp) > 7) {
                $fone = Phone::where([
                    ['phoneNumber', '=', $mp],
                ])->withTrashed()->first();
            } else {
                $fone = null;
            }

            if ($mp !== null && strlen($wp) > 7 && $fone === null) {
                $fone = new Phone;
                $fone->personID = $p->personID;
                $fone->phoneNumber = $mp;
                $fone->phoneType = 'Mobile';
                $fone->creatorID = auth()->user()->id;
                $fone->updaterID = auth()->user()->id;
            // $fone->save();
            } elseif (strlen($mp) > 7 && $fone !== null) {
                // if ($fone->personID != $p->personID) {
                //     $fone->debugNote = "ugh!  Was: $fone->personID; Should be: $p->personID";
                //     $fone->personID  = $p->personID;
                //     $fone->save();
                // }
            }

            // $ps               = new PersonStaging;
            // $ps->personID     = $p->personID;
            // $ps->prefix       = $prefix;
            // $ps->firstName    = $first;
            // $ps->midName      = $midName;
            // $ps->lastName     = $last;
            // $ps->suffix       = $suffix;
            // $ps->login        = $p->login;
            // $ps->title        = $title;
            // $ps->compName     = $compName;
            // $ps->defaultOrgID = $currentPerson->defaultOrgID;
            // $ps->creatorID    = auth()->user()->id;
            // $ps->save();
        }

        request()->session()->flash('alert-success', trans('messages.admin.upload.loaded',
            ['what' => trans('messages.admin.upload.mbrdata'), 'count' => $count]));
        */
    }

    public function getProcessedRowCount(): int
    {
        return $this->row_count;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function rules(): array
    {
        return [
            'pmi_id' => Rule::requiredIf(1),
            'primary_email' => Rule::requiredIf(1),
            'alternate_email' => Rule::requiredIf($this->em1 === null),
        ];
    }
}

<?php
/**
 * Comment: Added to have some global functions
 * Created: 8/25/2017
 */

use App\Address;
use App\Email;
use App\Models\Ticketit\TicketOver;
use App\Org;
use App\OrgPerson;
use App\Person;
use App\User;
use Carbon\Carbon;
use GrahamCampbell\Flysystem\Facades\Flysystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;

/**
 * Takes the html contents from the summernote input field and parses out uploaded images for
 * storage in AWS media area associated with Org and updates the html to reference image URLs
 *
 * @param $html
 * @param Org $org
 * @return string
 */
function extract_images($html, $orgID)
{
    $dom     = new \DOMDocument();
    $org     = Org::find($orgID);
    $updated = 0;

    try {
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_PARSEHUGE);

        $images = $dom->getElementsByTagName('img');

        foreach ($images as $img) {
            $src = $img->getAttribute('src');

            if (preg_match('/data:image/', $src)) {
                $updated = 1;
                // get the mimetype
                preg_match('/data:image\/(?<mime>.*?)\;/', $src, $groups);
                $mimetype = $groups['mime'];

                // Generating a random filename
                $filename = $img->getAttribute('data-filename');
                $filepath = "$org->orgPath/uploads/$filename";

                // @see http://image.intervention.io/api/
                $image = Image::make($src)
                // resize if required
                /* ->resize(300, 200) */
                    ->encode($mimetype, 100); // encode file to the specified mimetype

                //Flysystem::connection('s3_media')->put($event_filename, $contents);
                $s3m = Flysystem::connection('s3_media');
                //$s3m->put($filename, $image, ['visibility' => AdapterInterface::VISIBILITY_PUBLIC]);
                $s3m->put($filepath, $image->__toString());
                $new_src = $s3m->getAdapter()->getClient()->getObjectURL(env('AWS_BUCKET3'), $filepath);

                $img->removeAttribute('src');
                $img->removeAttribute('data-filename');
                $img->setAttribute('src', $new_src);
            }
        }
        if ($updated) {
            return $dom->saveHTML();
        } else {
            return $html;
        }
    } catch (Exception $exception) {
        request()->session()->flash('alert-danger', trans('messages.errors.html_error') . "<br /><pre>$exception</pre>");
        return $html;
    }
}

/**
 * Takes a model indicator and an array of variables, usually just 1, and performs a rudimentary existence check
 *
 * @param $model        Values: p for Person, e for Email, op for OrgPerson
 * @param $doFlash          1 if flash message should be set
 * @param $var_array    Contents:
 *                      + p:  firstName, lastName, login
 *                      + e:  login
 *                      + op: PMI ID
 */
function check_exists($model, $doFlash, $var_array)
{
    $details = "<ul>";
    switch ($model) {
        case 'p':
            list($first, $last, $login) = $var_array;
            $p                          = Person::where([
                ['firstName', '=', $first],
                ['lastName', '=', $last],
            ])
                ->orWhere('login', '=', $login)
                ->orWhereHas('emails', function ($q) use ($login) {
                    $q->where('emailADDR', '=', $login);
                })->get();
            if (count($p) > 0) {
                $details .= "<li>$first, $last, $login</li>";
                foreach ($p as $x) {
                    $existing = trans('messages.errors.existing_account', ['f' => $x->firstName, 'l' => $x->lastName, 'e' => $x->login]);
                    $details .= "<li>$existing</li>";
                }
                $details .= "</ul>";

                if ($doFlash) {
                    request()->session()->flash('alert-warning', trans_choice('messages.errors.exists', $model, ['details' => $details]));
                }
                return 1;
            }
            break;
        case 'e':
            list($email) = $var_array;
            $e           = Email::where('emailADDR', '=', $email)->first();
            if (null !== $e) {
                $p = Person::find($e->personID);
                $details .= '<li>' . $email . "</li>";
                $existing = trans('messages.errors.existing_account', ['f' => $p->firstName, 'l' => $p->lastName, 'e' => $p->login]);
                $details .= "<li>$existing</li>";
                $details .= "</ul>";
                if ($doFlash) {
                    request()->session()->flash('alert-warning', trans_choice('messages.errors.exists', $model, ['details' => $details]));
                }
                return 1;
            }
            break;
        case 'op':
            list($pmiID) = $var_array;
            if ($pmiID !== null) {
                $op = OrgPerson::where('OrgStat1', '=', $pmiID)->first();
                if (null !== $op) {
                    $p = Person::find($op->personID);
                    $details .= '<li>' . $op->OrgStat1 . "</li>";
                    $existing = trans('messages.errors.existing_account', ['f' => $p->firstName, 'l' => $p->lastName, 'e' => $p->login]);
                    $details .= "<li>$existing</li>";
                    $details .= "</ul>";
                    if ($doFlash) {
                        request()->session()->flash('alert-warning', trans_choice('messages.errors.exists', $model, ['details' => $details]));
                    }
                    return 1;
                }
            }
            break;
    }
    return 0;
}

/**
 * pLink: returns a URL string to a profile on the registration ID
 *
 * @param $regID
 * @param $personID
 * @return string
 */
function plink($regID, $personID)
{
    return '<a href="' . env('APP_URL') . '/profile/' . $personID . '">' . $regID . "</a>";
}

/**
 * et_translate: array_map function to apply a trans_choice if a translation exists for the term
 */
function et_translate($term)
{
    $x = 'messages.event_types.';
    if (Lang::has($x . $term)) {
        return trans_choice($x . $term, 1);
    } else {
        return $term;
    }
}

/**
 * li_print_array: convert an array into a html-formatted list
 * @param $array
 * @param $type
 * @return string
 */
function li_print_array($array, $type)
{
    //dd($array);
    switch ($type) {
        case "ol":
            $start = "<OL>";
            $end   = "</OL>";
            break;
        case "ul":
            $start = "<UL>";
            $end   = "</UL>";
            break;
    }
    $output = $start;
    foreach ($array as $item) {
        $reg  = $item['reg'];
        $name = $item['name'];
        $form = Form::open(['method' => 'delete', 'route' => ['cancel_registration', $reg->regID, $reg->regfinance->regID]]);
        $form .= Form::submit(trans('messages.buttons.reg_can'), array('class' => 'btn btn-primary btn-xs'));
        $form .= Form::close();
        $output .= "<li>$name $form</li>";
    }
    $output .= $end;
    return $output;
}

/*

Deleting - no need to have had this helper...

 * into_array: turns a comma-delimited string into an array
 * @param $string
 * @param $delimeter
 * @return array
function into_array($string, $delimeter) {
return(explode($delimeter, $string));
}
 */

/**
 * assoc_email returns 0 or 1 based on whether the $email address is associated with Person $p
 * @param $email
 * @param $p
 * @return boolean
 */
function assoc_email($email, $p)
{
    $e = Email::where('emailADDR', '=', $email)->first();
    if (null === $e || $e->personID != $p->personID) {
        return 0;
    } else {
        return 1;
    }
}

if (!function_exists('getAgentList')) {
    /**
     * get ticketit agent list from ticket or from current user
     * @param  ticket collection $ticket
     * @return array of agent
     */
    function getAgentList($ticket = null)
    {
        $orgId       = 0;
        $agent_lists = ['auto' => 'Auto Select'];
        if (empty($ticket)) {
            $person = Person::find(auth()->user()->id);
            $orgId  = $person->defaultOrgID;
        } else {
            $orgId = $ticket->orgId;
        }

        $user = User::where('id', auth()->user()->id)->get()->first();
        //get list of admins only for use with admin role
        if ($user->hasRole(['Admin']) && !$user->hasRole(['Developer'])) {
            $admin_agents = Person::whereIn('personID', function ($q) use ($orgId) {
                $q->select('user_id')
                    ->from('role_user')
                    ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
                    ->where('roles.name', 'Admin')
                    ->where('roles.orgId', $orgId);
            })->whereNotIn('personID', function ($q) use ($orgId) {
                $q->select('user_id')
                    ->from('role_user')
                    ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
                    ->where('roles.name', '=', 'Developer');
            })->get()->pluck('login', 'personID')->toArray();

            $agent_lists['auto_dev'] = 'Developer Queue';
            if (is_array($admin_agents)) {
                $agent_lists += $admin_agents;
            }
            return $agent_lists;
        }

        $admin_agents = Person::whereIn('personID', function ($q) use ($orgId) {
            $q->select('user_id')
                ->from('role_user')
                ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
                ->where('roles.name', 'Admin')
                ->where('roles.orgId', $orgId);
        })->get()->pluck('login', 'personID')->toArray();

        $dev_agents = Person::whereIn('personID', function ($q) {
            $q->select('user_id')
                ->from('role_user')
                ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
                ->where('roles.name', 'Developer');
        })->get()->pluck('login', 'personID')->toArray();
        $dev_agents = array_map(function ($value) {
            return $value . '(Developer)';
        }, $dev_agents);

        $agent_lists['auto_dev'] = 'Developer Queue';

        if (is_array($dev_agents)) {
            $agent_lists += $dev_agents;
        }

        if (is_array($admin_agents)) {
            foreach ($admin_agents as $key => $value) {
                if (array_key_exists($key, $agent_lists)) {
                    $agent_lists[$key] = $value . ' (Admin and Developer)';
                } else {
                    $agent_lists[$key] = $value . ' (Admin)';
                }
            }
        }
        return $agent_lists;
    }
}

if (!function_exists('getActiveTicketCountUser')) {
    /**
     * get logged in user active ticket count
     * @return int ticket count
     */
    function getActiveTicketCountUser()
    {
        $person = Person::find(auth()->user()->id);
        $orgId  = $person->defaultOrgID;
        return TicketOver::where(['user_id' => auth()->user()->id, 'user_read' => 0, 'orgId' => $orgId])
            ->whereNull('completed_at')
            ->get()->count();
    }
}

if (!function_exists('showActiveTicketUser')) {
    function showActiveTicketUser()
    {
        $person = Person::find(auth()->user()->id);
        $orgId  = $person->defaultOrgID;
        return TicketOver::where(['user_id' => auth()->user()->id, 'orgId' => $orgId])
            ->whereNull('completed_at')
            ->get()->count();
    }
}

if (!function_exists('markReadActiveTicketCountUser')) {
    /**
     * mark logged user all open ticket as read
     * @return bool
     */
    function markReadActiveTicketCountUser()
    {
        $person = Person::find(auth()->user()->id);
        $orgId  = $person->defaultOrgID;
        return TicketOver::where(['user_id' => auth()->user()->id, 'user_read' => 0, 'orgId' => $orgId])
            ->whereNull('completed_at')->update(['user_read' => 1]);
    }
}

if (!function_exists('markUnreadTicketUser')) {
    /**
     * make a specific user ticket as unread
     * @param  int $ticket_id ticket id
     * @return bool
     */
    function markUnreadTicketUser($ticket_id)
    {
        if (empty($ticket_id)) {
            return;
        }
        return TicketOver::where(['id' => $ticket_id])
            ->update(['user_read' => 0]);
    }
}

if (!function_exists('getActiveTicketCountAgent')) {
    /**
     * get all active ticket of loggedin user
     * @return int count
     */
    function getActiveTicketCountAgent()
    {
        $person = Person::find(auth()->user()->id);
        $orgId  = $person->defaultOrgID;
        return TicketOver::where(['agent_id' => auth()->user()->id, 'agent_read' => 0, 'orgId' => $orgId])
            ->whereNull('completed_at')
            ->get()->count();
    }
}

if (!function_exists('getTicketCategories')) {
    /**
     * get all active ticket of loggedin user
     * @return int count
     */
    function getTicketCategories()
    {
        list($priorities, $categories) = app(App\Http\TicketitControllers\TicketsControllerOver::class)->PCS();
        return $categories;
    }
}

if (!function_exists('getTicketPriorities')) {
    /**
     * get all active ticket of loggedin user
     * @return int count
     */
    function getTicketPriorities()
    {
        list($priorities, $categories) = app(App\Http\TicketitControllers\TicketsControllerOver::class)->PCS();
        return $priorities;
    }
}
if (!function_exists('storeImportDataDB')) {

    $starttime;
    $phone_master;
    $email_master;
    $address_master;
    $person_staging_master;
    /*
    all insert and update are commented from this code for testing on live
     */
    function storeImportDataDB($row, $currentPerson, $count_g = null)
    {
        DB::connection()->disableQueryLog();

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
                // $p                = Person::create($p_array); insert

                // $u_array = [
                //     'id'    => $p->personID,
                //     'login' => $em1,
                //     'name'  => $em1,
                //     'email' => $em1,
                // ];
                // // $u = User::create($u_array); // create
                // // $this->timeMem('7 u insert');
                // $this->insertEmail($personID = $p->personID, $email = $em1, $primary = 1);

                // Otherwise, try with email #2
            } elseif ($em2 !== null && $em2 != '' && $em2 != ' ' && $p->login === null) {
                $p->login = $em2;
                $p->save();
                $u->id    = $p->personID;
                $u->login = $em2;
                $u->name  = $em2;
                $u->email = $em2;
                $u->save();
                // $this->insertEmail($personID = $p->personID, $email = $em2, $primary = 1);
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
                // $this->insertEmail($personID = $p->personID, $email = $em2, $primary = 0);
            } elseif ($em2 !== null && $em2 == strtolower($chk2)) {
                if ($emchk2->personID != $p->personID) {
                    $emchk2->debugNote = "ugh!  Was: $emchk2->personID; Should be: $p->personID";
                    $emchk2->personID  = $p->personID;
                    // $emchk2->save(); update

                }

            }

        } elseif ($op->isNotEmpty() || $any_op->isNotEmpty()) {
            // There was an org-person record (found by $OrgStat1 == PMI ID) for this chapter/orgID
            if ($op->isNotEmpty()) {
                // For modularity, updating the $op record will happen below as there are no dependencies
                // $p = Person::where(['personID' => $op[0]->personID])->get();
                $p = DB::table('person')->where(['personID' => $op->get('personID')])->limit(1)->get();
                // dd($p->first());
                // $this->timeMem('10 op and any op check 2142');
                $p = $p->first();
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
            if ($em1 !== null && $em1 != "" && $em1 != " " && $em1 != strtolower($chk1) && $em1 != strtolower($chk2)) {
                // $this->insertEmail($personID = $p->personID, $email = $em1, $primary = 0);
            } elseif ($em1 !== null && $em1 == strtolower($chk1)) {
                if ($emchk1[0]->personID != $p->personID) {
                    $emchk1[0]->personID  = $p->personID;
                    $emchk1[0]->debugNote = "ugh!  Was: $emchk1[0]->personID; Should be: $p->personID";
                    // DB::table('person-email')->where(['personID' => $emchk1[0]->personID])
                    // ->update(['personID' => $p->personID, 'debugNote' => $emchk1[0]->debugNote]); //update
                }
            }
            if ($em2 !== null && $em2 != "" && $em2 != " " && $em2 != strtolower($chk1) && $em2 != strtolower($chk2) && $em2 != $em1) {
                // $this->insertEmail($personID = $p->personID, $email = $em2, $primary = 0);
            } elseif ($em2 !== null && $em2 == strtolower($chk2)) {
                if ($emchk2->personID != $p->personID) {
                    $emchk2->debugNote = "ugh!  Was: $emchk2->personID; Should be: $p->personID";
                    $emchk2->personID  = $p->personID;
                    // DB::table('person-email')->where(['personID' => $emchk2[0]->personID])
                    //     ->update(['personID' => $p->personID, 'debugNote' => $emchk2[0]->debugNote]); update

                }
            }
            // } elseif ($emchk1->isNotEmpty() && $em1->isNotEmpty() && $em1 != '' && $em1 != ' ') {
        } elseif ($emchk1->isNotEmpty() && !empty($em1) && $em1 != '' && $em1 != ' ') {
            $emchk1 = $emchk1[0];
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
                if ($op->isEmpty()) {$need_op_record = 1;}
            } catch (Exception $ex) {
                // dd([$emchk1, $em1]);
            }
            // We have an email record match so we should NOT rely on firstName/lastName matching at all
            $pchk = null;
        } elseif ($emchk2->isNotEmpty() && !empty($em2) && $em2 != '' && $em2 != ' ') {
            $emchk2 = $emchk2[0];
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
            $p                      = $pchk[0];
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

                // One day: think about how to auto-populate indName field using compName

                $ary['updaterID']    = auth()->user()->id;
                $ary['defaultOrgID'] = $currentPerson->defaultOrgID;
                // DB::table('person')->where('personID', $p->personID)->update($ary); update
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
            $newOP = new OrgPerson;
            // $newOP->orgID    = $p->defaultOrgID;
            // $newOP->personID = $p->personID;
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

            if (!empty($row['pmi_join_date'])) {
                $newOP->RelDate1 = Carbon::createFromFormat('d/m/Y', $row['pmi_join_date'])->toDateTimeString();
            }
            if (!empty($row['chapter_join_date'])) {
                $newOP->RelDate2 = Carbon::createFromFormat('d/m/Y', $row['chapter_join_date'])->toDateTimeString();
            }
            if (!empty($row['pmi_expiration'])) {
                $newOP->RelDate3 = Carbon::createFromFormat('d/m/Y', $row['pmi_expiration'])->toDateTimeString();
            }
            if (!empty($row['pmi_expiration'])) {
                $newOP->RelDate4 = Carbon::createFromFormat('d/m/Y', $row['chapter_expiration'])->toDateTimeString();
            }
            $newOP->creatorID = auth()->user()->id;
            // $newOP->save(); update
            // $this->timeMem('19 new po update 2312');
            // if ($p->defaultOrgPersonID === null) {
                // DB::table('person')->where('personID', $p->personID)->update(['defaultOrgPersonID' => $newOP->id]);
                // $this->timeMem('20 person update 2315');
            // }
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
                // DB::table('org-person')->where('id', $newOP->id)->update($ary); update

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

                // $this->insertAddress(
                //     $personID = $p->personID,
                //     $addresstype = trim(ucwords($row['preferred_address_type'])),
                //     $addr1 = trim(ucwords($row['preferred_address'])),
                //     $city = trim(ucwords($row['city'])),
                //     $state = trim(ucwords($row['state'])),
                //     $zip = $z,
                //     $country = $cntry_id);
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
                        if ($value->phoneID == $p->personID) {
                            $ary = [
                                'debugNote' => "ugh!  Was: $value->personID; Should be: $p->personID",
                                'personID'  => $p->personID,
                            ];
                            // DB::table('person-phone')->where('id', $value->phoneID)->update($ary); //update
                        }
                    }
                } else {
                    if (strlen($row['home_phone']) > 7) {
                        // $this->insertPhone($personid = $p->personID, $phonenumber = $row['home_phone'], $phonetype = 'Home');
                    }

                    if (strlen($row['work_phone']) > 7) {
                        // $this->insertPhone($personid = $p->personID, $phonenumber = $row['work_phone'], $phonetype = 'Work');
                    }

                    if (strlen($row['mobile_phone']) > 7) {
                        // $this->insertPhone($personid = $p->personID, $phonenumber = $row['mobile_phone'], $phonetype = 'Mobile');
                    }
                }
            }

            // $this->insertPersonStaging($p->personID, $prefix, $first, $midName, $last, $suffix, $p->login, $title, $compName, $currentPerson->defaultOrgID);
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

}

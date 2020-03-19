<?php
/**
 * Comment: Added to have some global functions
 * Created: 8/25/2017
 */

use App\Email;
use App\Models\Ticketit\TicketOver;
use App\Org;
use App\OrgPerson;
use App\Person;
use GrahamCampbell\Flysystem\Facades\Flysystem;
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
        $orgId = 0;
        if (empty($ticket)) {
            $person = Person::find(auth()->user()->id);
            $orgId  = $person->defaultOrgID;
        } else {
            $orgId = $ticket->orgId;
        }
        $dev_agents = Person::whereIn('personID', function ($q) {
            $q->select('user_id')
                ->from('role_user')
                ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
                ->where('roles.name', 'Developer');
        })->get()->pluck('login', 'personID')->toArray();

        $admin_agents = Person::whereIn('personID', function ($q) use ($orgId) {
            $q->select('user_id')
                ->from('role_user')
                ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
                ->where('roles.name', 'Admin')
                ->where('roles.orgId', $orgId);
        })->get()->pluck('login', 'personID')->toArray();

        $dev_agents = array_map(function ($value) {
            return $value . '(Developer)';
        }, $dev_agents);

        $agent_lists = ['auto' => 'Auto Select'];
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
        return TicketOver::where(['user_id' => auth()->user()->id, 'user_read' => 0])
            ->whereNull('completed_at')
            ->get()->count();
    }
}

if (!function_exists('showActiveTicketUser')) {
    function showActiveTicketUser()
    {
        return TicketOver::where(['user_id' => auth()->user()->id])
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
        return TicketOver::where(['user_id' => auth()->user()->id, 'user_read' => 0])
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
        return TicketOver::where(['agent_id' => auth()->user()->id, 'agent_read' => 0])
            ->whereNull('completed_at')
            ->get()->count();
    }
}

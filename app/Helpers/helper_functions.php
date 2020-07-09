<?php
/**
 * Comment: Added to have some global functions
 * Created: 8/25/2017
 */

use App\Address;
use App\Email;
use App\EmailList;
use App\Event;
use App\Location;
use App\Models\Ticketit\TicketOver;
use App\Org;
use App\OrgPerson;
use App\Person;
use App\User;
use Carbon\Carbon;
use GrahamCampbell\Flysystem\Facades\Flysystem;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;
use PHPHtmlParser\Dom;
use Spatie\Browsershot\Browsershot;
use Spatie\Image\Manipulations;

// $client = new Client();
/**
 * Takes the html contents from the summernote input field and parses out uploaded images for
 * storage in AWS media area associated with Org and updates the html to reference image URLs
 *
 * @param $html
 * @param Org $org
 * @return string
 */
if (!function_exists('extract_images')) {
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

if (!function_exists('check_exists')) {
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
}

/**
 * pLink: (profileLink) returns a URL string to a profile on the registration ID
 *
 * @param $regID
 * @param $personID
 * @return string
 */

if (!function_exists('pLink')) {
    function plink($regID, $personID)
    {
        return '<a href="' . env('APP_URL') . '/profile/' . $personID . '">' . $regID . "</a>";
    }
}

/**
 * et_translate: array_map function to apply a trans_choice if a translation exists for the term
 */

if (!function_exists('et_translate')) {
    function et_translate($term)
    {
        $x = 'messages.event_types.';
        if (Lang::has($x . $term)) {
            return trans_choice($x . $term, 1);
        } else {
            return $term;
        }
    }
}

/**
 * li_print_array: convert an array into a html-formatted list
 * @param $array
 * @param $type
 * @return string
 */

if (!function_exists('li_print_array')) {
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
}

/**
 * assoc_email returns 0 or 1 based on whether the $email address is associated with Person $p
 * @param $email
 * @param $p
 * @return boolean
 */

if (!function_exists('assoc_email')) {
    function assoc_email($email, $p)
    {
        $e = Email::where('emailADDR', '=', $email)->first();
        if (null === $e || $e->personID != $p->personID) {
            return 0;
        } else {
            return 1;
        }
    }
}

/**
 * @param Request $request
 * @param Event $event
 * @param Person $current_person
 * @return Location | null
 *
 * location_triage evaluates location data entered while creating/updating event by:
 * 1. Checking if a locationID was set by selecting from the dropdown
 * 2. Checking whether data was entered into visible form fields
 * 3. Checking if there are any discrepancies (changes) to data in the form fields indicating the need to update
 */

if (!function_exists('location_triage')) {
    function location_triage(Request $request, Event $event, Person $current_person)
    {
        //$loc = null;
        $locationID  = request()->input('locationID');
        $loc_virtual = request()->input('virtual');

        if (empty($loc_virtual)) {
            $loc_virtual = 0;
        }

        $locName = request()->input('locName');
        $addr1   = request()->input('addr1');
        $addr2   = request()->input('addr2');
        $city    = request()->input('city');
        $state   = request()->input('state');
        $zip     = request()->input('zip');

        switch ($locationID) {
            case null:
                // No location was selected so this is, possibly, a new location.
                // Determine if there is any preventitive dupe-checking possible
                $loc = Location::firstOrNew(
                    [
                        'locName' => $locName,
                        'orgID'   => $event->orgID,
                    ]
                );
                break;

            case $event->locationID:
                // If the selected locationID matches one already associated with the event, update fields unless orgID=1
                $loc = Location::find($event->locationID);
                break;

            case !empty($locationID):
                // If the selected locationID does not match one already associated with the event, change association
                // Update location fields unless orgID=1
                $loc = Location::find($locationID);
                break;

        }

        if ($loc->orgID == $event->orgID) {
            $loc->locName   = $locName;
            $loc->addr1     = $addr1;
            $loc->addr2     = $addr2;
            $loc->city      = $city;
            $loc->state     = $state;
            $loc->zip       = $zip;
            $loc->isVirtual = $loc_virtual;
            $loc->updaterID = $current_person->personID;
            $loc->save();
        } else {
            // orgID is probably 1 and so warn that values cannot be edited
            request()->session()->flash('alert-warning', trans('messages.messages.loc_edit'));
        }

        $loc->save();

        return $loc;

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

if (!function_exists('get_template_builder_blocks_category')) {
    /**
     * get template builder block category from database
     * @return array of email block category
     */
    function get_template_builder_category()
    {
        return App\Models\EmailBlockCategory::all()->toArray();
    }
}

if (!function_exists('get_template_builder_blocks_category')) {
    /**
     * get template blocks by category id
     * @param  integer $email_cat_id  email category id
     * @return array               of category blocks
     */
    function get_template_builder_block_category($email_cat_id)
    {
        return App\Models\EmailBlock::where(['cat_id' => $email_cat_id, 'is_active' => 1])->get()->toArray();
    }
}

if (!function_exists('isDate')) {
    /**
     * check if given string is date type
     * @param  string  $date_str date
     * @return boolean        true/false
     */
    function isDate($date_str)
    {
        if (!$date_str) {
            return false;
        } else {
            $date = date_parse($date_str);
            if ($date['error_count'] == 0 && $date['warning_count'] == 0) {
                return checkdate($date['month'], $date['day'], $date['year']);
            } else {
                return false;
            }
        }
    }
}

if (!function_exists('requestBin')) {
    /**
     * send data to request bin for queue debug
     * @param  array $data
     */
    function requestBin($data)
    {
        return;
        // API URL
        $url = 'https://enpfjlvpu0oo.x.pipedream.net';
        // Create a new cURL resource
        $ch = curl_init($url);
        // Setup request to send json via POST
        $payload = json_encode(array("user" => $data));
        // Attach encoded JSON string to the POST fields
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        // Set the content type to application/json
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        // Return response instead of outputting
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Execute the POST request
        $result = curl_exec($ch);
        // Close cURL resource
        curl_close($ch);
    }
}

if (!function_exists('replaceUserDataInEmailTemplate')) {
    /**
     * replace text placeholder with actual data from database
     * @param  string  $email       user email whose data needed to be replaced by placeholder
     * @param  object  $campaign    the campaign object
     * @param  boolean $for_preview true if using for preview only false while sending actual email
     * @param  string  $raw_html    html string used when for_preview is true
     * @return string               replaced html
     */
    function replaceUserDataInEmailTemplate($email, $campaign, $for_preview = false, $raw_html = null, $note = null)
    {
        // $start = microtime(true);
        $person         = '';
        $organization   = '';
        $org_name       = '';
        $pre_header_str = '<table width="100%" cellspacing="0" cellpadding="0" border="0" style="background:rgb(233,234,234) none repeat scroll 0% 0%/auto padding-box border-box"><tbody><tr><td><div style="margin:0 auto;width:600px;padding:0px">
                                    <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:rgb(255,255,255);width:600px;border-spacing:0px;border-collapse:collapse" align="center">
                <tbody>
                    <tr>
                        <td align="left" style="padding:10px 50px;font-family:Arial;font-size:13px;color:rgb(0,0,0);line-height:22px;border-collapse:collapse;text-align:center">
                            <div>
                                ##toreplace##
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table></div></td></tr></tbody></table>';
        if ($for_preview) {
            if (!empty($raw_html)) {
                $person       = Person::where(['personID' => auth()->user()->id])->with('orgperson')->get()->first();
                $organization = Org::where('orgID', $person->defaultOrgID)->select('orgName')->get()->first();
                if (!empty($campaign)) {
                    if (!empty($campaign->preheader)) {
                        $preheader_html = str_replace('##toreplace##', $campaign->preheader, $pre_header_str);
                        $raw_html       = $preheader_html . $raw_html;
                    }
                }
            } else {
                return $raw_html;
            }
        } else {
            $raw_html = '';
            //if in case campaign content is empty pick individual blocks
            if (empty($campaign->content)) {
                foreach ($campaign->template_blocks as $key => $value) {
                    $raw_html .= $value->content;
                }
            } else {
                if (!empty($campaign->preheader)) {
                    $preheader_html = str_replace('##toreplace##', $campaign->preheader, $pre_header_str);
                    $raw_html       = $preheader_html . $campaign->content;
                } else {
                    $raw_html = $campaign->content;
                }
            }
            $person       = Person::where(['login' => $email, 'defaultOrgID' => $campaign->orgID])->with('orgperson')->get()->first();
            $organization = Org::where('orgID', $campaign->orgID)->select('orgName')->get()->first();
        }
        if (empty($person) || empty($organization)) {
            return $raw_html;
        }
        if (!empty($organization)) {
            $org_name = $organization->orgName;
        }
        $mapping = [
            '[PREFIX]'           => $person->prefix,
            '[FIRSTNAME]'        => $person->firstName,
            '[MIDDLENAME]'       => $person->midName,
            '[LASTNAME]'         => $person->lastName,
            '[SUFFIX]'           => $person->suffix,
            '[PREFNAME]'         => $person->prefName,
            '[LOGINEMAIL]'       => $person->login,
            '[ORGANIZATIONNAME]' => $org_name,
            '[TITLE]'            => $person->title,
            '[COMPANYNAME]'      => $person->compName,
            '[INDUSTRY]'         => $person->indName,
            '[EXPERINCE]'        => $person->experience,
            '[ALLERGENNOTE]'     => $person->allergenNote,
            '[SPECIALNEEDS]'     => $person->specialNeeds,
            '[CHAPTERROLE]'      => $person->chapterRole,
            '[AFFILIATION]'      => $person->affiliation,
            '[TWITTERHANDLE]'    => $person->twitterHandle,
            '[CERTIFICATIONS]'   => $person->certifications,
            '[OSN1]'             => $person->orgperson->OrgStat1,
            '[OSN2]'             => $person->orgperson->OrgStat2,
            '[OSN3]'             => $person->orgperson->OrgStat3,
            '[OSN4]'             => $person->orgperson->OrgStat4,
            '[OSN5]'             => $person->orgperson->OrgStat5,
            '[OSN6]'             => $person->orgperson->OrgStat6,
            '[OSN7]'             => $person->orgperson->OrgStat7,
            '[OSN8]'             => $person->orgperson->OrgStat8,
            '[OSN9]'             => $person->orgperson->OrgStat9,
            '[OSN10]'            => $person->orgperson->OrgStat10,
            '[ODN1]'             => $person->orgperson->RelDate1,
            '[ODN2]'             => $person->orgperson->RelDate2,
            '[ODN3]'             => $person->orgperson->RelDate3,
            '[ODN4]'             => $person->orgperson->RelDate4,
            '[ODN5]'             => $person->orgperson->RelDate5,
            '[ODN6]'             => $person->orgperson->RelDate6,
            '[ODN7]'             => $person->orgperson->RelDate7,
            '[ODN8]'             => $person->orgperson->RelDate8,
            '[ODN9]'             => $person->orgperson->RelDate9,
            '[ODN10]'            => $person->orgperson->RelDate10,
        ];
        // $rep = str_replace(array_keys($mapping), $mapping, $raw_html);
        // $test = (microtime(true) - $start) . "Seconds";
        // dd($test);
        return str_replace(array_keys($mapping), $mapping, $raw_html);
    }
}

if (!function_exists('generateEmailTemplateThumbnail')) {
    /**
     * Generate thumbnail from email html
     * @param  string $html     email html
     * @param  object $campaign campaign object for name
     * @return string           file path
     */
    function generateEmailTemplateThumbnail($html, $campaign)
    {
        if (empty($html) || empty($campaign)) {
            return false;
        }
        $file_name = generateEmailTemplateThumbnailName($campaign);
        $html      = replaceUserDataInEmailTemplate($email = null, $campaign_obj = null, $for_preview = true, $raw_html = $html);
        $org       = Org::where('orgID', $campaign->orgID)->get()->first();
        $path      = getAllDirectoryPathFM($org);
        $full_path = $path['campaign'] . '/thumb/' . $file_name;
        $img       = Browsershot::html($html)
            ->fullPage()
            ->fit(Manipulations::FIT_CONTAIN, 70, 120)
            ->addChromiumArguments(['no-sandbox', 'disable-setuid-sandbox'])
            ->screenshot();
        // ->save(Storage::disk('local')->path($full_path));
        Storage::disk(getDefaultDiskFM())->put($full_path, $img);
        return Storage::disk(getDefaultDiskFM())->url($full_path);
    }
}

if (!function_exists('generateEmailTemplateThumbnailName')) {
    /**
     * generate email template thumbnail name
     * @param  object $campaign campaign object
     * @return string           template thumbnail name
     */
    function generateEmailTemplateThumbnailName($campaign)
    {
        //format orgid-campaignid-created date time stamp to avoid storing unnecessary data in db
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $campaign->createDate);
        return $campaign->orgID . '-' . $campaign->campaignID . '-' . $date->timestamp . '.png';
    }
}

if (!function_exists('getEmailTemplateThumbnailName')) {
    /**
     * same as generate just for sake of naming convension
     * @param  [type] $campaign [description]
     * @return [type]           [description]
     */
    function getEmailTemplateThumbnailName($campaign)
    {
        //format orgid-campaignid-created date time stamp to avoid storing unnecessary data in db
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $campaign->createDate);
        return $campaign->orgID . '-' . $campaign->campaignID . '-' . $date->timestamp . '.png';
    }
}

if (!function_exists('getEmailTemplateThumbnailURL')) {
    /**
     * get url for email template thumbnail it also check if the thumbnail does not exist it will show mcentric logo instead
     * @param  object $campaign campaign object
     * @return string           URL for thumbnail
     */
    function getEmailTemplateThumbnailURL($campaign)
    {
        $path      = getAllDirectoryPathFM();
        $file_name = getEmailTemplateThumbnailName($campaign);
        if (Storage::disk(getDefaultDiskFM())->exists($path['campaign'] . '/thumb/' . $file_name)) {
            return Storage::disk(getDefaultDiskFM())->url($path['campaign'] . '/thumb/' . $file_name);
        } else {
            return url('images/mCentric_square.png');

        }
    }
}

if (!function_exists('getAllDirectoryPathFM')) {
    /**
     * all static directory path for filemanger this has a system wide impact
     * @param  object $org org model if available
     * @return array      all path that are created
     */
    function getAllDirectoryPathFM($org = null)
    {
        if (empty($org)) {
            $currentPerson = Person::find(auth()->user()->id);
            $org           = $currentPerson->defaultOrg;
        }
        // $base_path        = $org->orgID . '/' . $org->orgPath . '/'; alternate approach
        $base_path = $org->orgPath . '/filemanager/';
        // $base_path              = $org->orgPath . '/';
        $path['event']          = Storage::disk(getDefaultDiskFM())->path($base_path . 'events_files');
        $path['campaign']       = Storage::disk(getDefaultDiskFM())->path($base_path . 'campaign_files');
        $path['campaign_thumb'] = Storage::disk(getDefaultDiskFM())->path($base_path . 'campaign_files/thumb');
        $path['orgPath']        = Storage::disk(getDefaultDiskFM())->path($org->orgPath);
        $path['orgPathFM']      = Storage::disk(getDefaultDiskFM())->path($org->orgPath . '/filemanager');
        return $path;
    }

}

if (!function_exists('generateDirectoriesForOrg')) {
    /**
     * generate default directory set for new organizations
     * @param  object $org organization object
     * @return null
     */
    function generateDirectoriesForOrg($org)
    {
        $path = getAllDirectoryPathFM($org);
        if (Storage::disk(getDefaultDiskFM())->exists($path['orgPath']) == false) {
            Storage::disk(getDefaultDiskFM())->makeDirectory($path['orgPath']);
        }
        foreach ($path as $key => $value) {
            if (Storage::disk(getDefaultDiskFM())->exists($value) == false) {
                $var = Storage::disk(getDefaultDiskFM())->makeDirectory($value);
            }
        }
    }
}

if (!function_exists('getDefaultPathFM')) {
    /**
     * for filemanager config default path for filemanager (do not change)
     * @return string folder path
     */
    function getDefaultPathFM()
    {
        $currentPerson = Person::find(auth()->user()->id);
        $org           = $currentPerson->defaultOrg;
        return Storage::disk(getDefaultDiskFM())->path($org->orgPath . '/filemanager');
    }
}

if (!function_exists('getDefaultDiskFM')) {
    /**
     * return value of default disk for file manager
     * @return string diskname
     */
    function getDefaultDiskFM()
    {
        return 's3_media';
    }
}
if (!function_exists('getAllDiskFM')) {
    /**
     * return all available disk for file manager
     * @return array of disk names
     */
    function getAllDiskFM()
    {
        // return ['public', 's3_receipts', 's3_media', 'events'];
        return ['s3_media'];
    }
}
if (!function_exists('getEmailList')) {
    /**
     * get count and email list for selected user org
     * @param  object  $currentPerson model object
     * @param  boolean $for_select    if needed for dropdown use true
     * @return array                 either only name and id or complete details
     */
    function getEmailList($currentPerson, $for_select = false)
    {
        $rows        = [];
        $lists       = EmailList::where('orgID', $currentPerson->defaultOrgID)->get();
        $select_rows = [];
        $today       = Carbon::now();
        foreach ($lists as $l) {
            $included   = explode(',', $l->included);
            $foundation = $l->foundation;
            // $foundation1 = array_shift($included);
            // dd($foundation,$foundation1);
            $excluded = explode(',', $l->excluded);

            // foundations are either filters (when $included !== null) or true foundations
            if ($included != null) {
                switch ($foundation) {
                    case "none":
                    case "everyone":
                        $c = Person::whereHas('orgs', function ($q) use ($currentPerson) {
                            $q->where('organization.orgID', $currentPerson->defaultOrgID);
                        })
                            ->whereHas('registrations', function ($q) use ($included, $excluded) {
                                $q->whereIn('eventID', $included);
                                $q->whereNotIn('eventID', $excluded);
                            })
                            ->distinct()
                            ->select('person.personID')
                            ->count();
                        break;
                    case "pmiid":
                        $c = Person::whereHas('orgs', function ($q) use ($currentPerson) {
                            $q->where('organization.orgID', $currentPerson->defaultOrgID);
                        })
                            ->whereHas('registrations', function ($q) use ($included, $excluded) {
                                $q->whereIn('eventID', $included);
                                $q->whereNotIn('eventID', $excluded);
                            })
                            ->whereHas('orgperson', function ($q) {
                                $q->whereNotNull('OrgStat1');
                            })
                            ->distinct()
                            ->select('person.personID')
                            ->count();
                        break;
                    case "nonexpired":
                        $c = Person::whereHas('orgs', function ($q) use ($currentPerson) {
                            $q->where('organization.orgID', $currentPerson->defaultOrgID);
                        })
                            ->whereHas('registrations', function ($q) use ($included, $excluded) {
                                $q->whereIn('eventID', $included);
                                $q->whereNotIn('eventID', $excluded);
                            })
                            ->whereHas('orgperson', function ($q) use ($today) {
                                $q->whereNotNull('OrgStat1');
                                $q->whereDate('RelDate4', '>=', $today);
                            })
                            ->distinct()
                            ->select('person.personID')
                            ->count();
                        break;
                }
            } else {
                // $included === null
                switch ($foundation) {
                    case "none":
                    // none with a null $included is not possible
                    case "everyone":
                        $c = Person::whereHas('orgs', function ($q) use ($currentPerson) {
                            $q->where('organization.orgID', $currentPerson->defaultOrgID);
                        })
                            ->whereDoesntHave('registrations', function ($q) use ($excluded) {
                                $q->whereIn('eventID', $excluded);
                            })
                            ->distinct()
                            ->select('person.personID')
                            ->count();
                        break;
                    case "pmiid":
                        $c = Person::whereHas('orgs', function ($q) use ($currentPerson) {
                            $q->where('organization.orgID', $currentPerson->defaultOrgID);
                        })
                            ->whereHas('registrations', function ($q) use ($excluded) {
                                $q->whereNotIn('eventID', $excluded);
                            })
                            ->whereHas('orgperson', function ($q) use ($today) {
                                $q->whereNotNull('OrgStat1');
                            })
                            ->distinct()
                            ->select('person.personID')
                            ->count();
                        break;
                    case "nonexpired":
                        $c = Person::whereHas('orgs', function ($q) use ($currentPerson) {
                            $q->where('organization.orgID', $currentPerson->defaultOrgID);
                        })
                            ->whereHas('registrations', function ($q) use ($excluded) {
                                $q->whereNotIn('eventID', $excluded);
                            })
                            ->whereHas('orgperson', function ($q) use ($today) {
                                $q->whereNotNull('OrgStat1');
                                $q->whereDate('RelDate4', '>=', $today);
                            })
                            ->distinct()
                            ->select('person.personID')
                            ->count();
                        break;
                }
            }
            $edit_link   = '<a href="' . url('list', $l->id) . '"><i aria-hidden="true" class="fa fa-edit">&nbsp;</i>Edit</a>';
            $delete_link = '<a href="javascript:void(0)" onclick="confim_delete(' . $l->id . ')"><i aria-hidden="true" class="fa fa-trash-alt">&nbsp;</i>Delete</a>';
            $links       = $edit_link . ' | ' . $delete_link;
            array_push($rows, [$l->listName, $l->listDesc, $c, $l->created_at->format('n/j/Y'), $links]);
            array_push($select_rows, ['id' => $l->id, 'name' => $l->listName, 'count' => $c]);
        }
        if ($for_select) {
            return $select_rows;
        } else {
            return $rows;
        }
    }
}
if (!function_exists('getDefaultEmailList')) {
    /**
     * get default email list (list from efcico corporation)
     * @param  object  $currentPerson default
     * @param  boolean $for_select    only for dropdown
     * @return array                  either only name and id or complete details
     */
    function getDefaultEmailList($currentPerson, $for_select = false)
    {
        $defaults    = EmailList::where('orgID', 1)->get();
        $rows        = [];
        $select_rows = [];
        foreach ($defaults as $l) {
            if ($l->foundation == 'everyone') {
                $c = Person::whereHas('orgs', function ($q) use ($currentPerson) {
                    $q->where('organization.orgID', $currentPerson->defaultOrgID);
                })->count();
            } elseif ($l->foundation == 'pmiid') {
                $c = Person::whereHas('orgs', function ($q) use ($currentPerson) {
                    $q->where('organization.orgID', $currentPerson->defaultOrgID);
                })->whereHas('orgperson', function ($q) {
                    $q->whereNotNull('OrgStat1');
                })->count();
            } elseif ($l->foundation == 'nonexpired') {
                $c = Person::whereHas('orgs', function ($q) use ($currentPerson) {
                    $q->where('organization.orgID', $currentPerson->defaultOrgID);
                })->whereHas('orgperson', function ($q) {
                    $q->whereDate('RelDate4', '>=', Carbon::now());
                })->count();
            } else {
                // For default lists, we shouldn't ever get here
                $c = 0;
            }
            array_push($rows, [$l->listName, $c, $l->created_at->format('n/j/Y')]);
            array_push($select_rows, ['id' => $l->id, 'name' => $l->listName, 'count' => $c]);
        }
        if ($for_select) {
            return $select_rows;
        } else {
            return $rows;
        }
    }
}

if (!function_exists('getEmailListContact')) {
    /**
     * get email list from email list management
     * @param  int $list_id list management id
     * @param  int $org_id  organization id
     * @return array        email id list
     */
    function getEmailListContact($list_id, $org_id)
    {
        $list       = EmailList::whereId($list_id)->get()->first();
        $rows       = [];
        $included   = explode(',', $list->included);
        $foundation = $list->foundation;
        $excluded   = explode(',', $list->excluded);
        $today      = Carbon::now();
        // foundations are either filters (when $included !== null) or true foundations
        if ($included != null) {
            switch ($foundation) {
                case "none":
                case "everyone":
                    $c = Person::whereHas('orgs', function ($q) use ($org_id) {
                        $q->where('organization.orgID', $org_id);
                    })
                        ->whereHas('registrations', function ($q) use ($included, $excluded) {
                            $q->whereIn('eventID', $included);
                            $q->whereNotIn('eventID', $excluded);
                        })
                        ->distinct()
                        ->select('person.personID', 'person.login')
                        ->get();
                    break;
                case "pmiid":
                    $c = Person::whereHas('orgs', function ($q) use ($org_id) {
                        $q->where('organization.orgID', $org_id);
                    })
                        ->whereHas('registrations', function ($q) use ($included, $excluded) {
                            $q->whereIn('eventID', $included);
                            $q->whereNotIn('eventID', $excluded);
                        })
                        ->whereHas('orgperson', function ($q) {
                            $q->whereNotNull('OrgStat1');
                        })
                        ->distinct()
                        ->select('person.personID', 'person.login')
                        ->get();
                    break;
                case "nonexpired":
                    $c = Person::whereHas('orgs', function ($q) use ($org_id) {
                        $q->where('organization.orgID', $org_id);
                    })
                        ->whereHas('registrations', function ($q) use ($included, $excluded) {
                            $q->whereIn('eventID', $included);
                            $q->whereNotIn('eventID', $excluded);
                        })
                        ->whereHas('orgperson', function ($q) use ($today) {
                            $q->whereNotNull('OrgStat1');
                            $q->whereDate('RelDate4', '>=', $today);
                        })
                        ->distinct()
                        ->select('person.personID', 'person.login')
                        ->get();
                    break;
            }
        } else {
            // $included === null
            switch ($foundation) {
                case "none":
                // none with a null $included is not possible
                case "everyone":
                    $c = Person::whereHas('orgs', function ($q) use ($org_id) {
                        $q->where('organization.orgID', $org_id);
                    })
                        ->whereDoesntHave('registrations', function ($q) use ($excluded) {
                            $q->whereIn('eventID', $excluded);
                        })
                        ->distinct()
                        ->select('person.personID', 'person.login')
                        ->get();
                    break;
                case "pmiid":
                    $c = Person::whereHas('orgs', function ($q) use ($org_id) {
                        $q->where('organization.orgID', $org_id);
                    })
                        ->whereHas('registrations', function ($q) use ($excluded) {
                            $q->whereNotIn('eventID', $excluded);
                        })
                        ->whereHas('orgperson', function ($q) use ($today) {
                            $q->whereNotNull('OrgStat1');
                        })
                        ->distinct()
                        ->select('person.personID', 'person.login')
                        ->get();
                    break;
                case "nonexpired":
                    $c = Person::whereHas('orgs', function ($q) use ($org_id) {
                        $q->where('organization.orgID', $org_id);
                    })
                        ->whereHas('registrations', function ($q) use ($excluded) {
                            $q->whereNotIn('eventID', $excluded);
                        })
                        ->whereHas('orgperson', function ($q) use ($today) {
                            $q->whereNotNull('OrgStat1');
                            $q->whereDate('RelDate4', '>=', $today);
                        })
                        ->distinct()
                        ->select('person.personID', 'person.login')
                        ->get();
                    break;
            } //switch end
        } //else end
        $email_list = [];
        foreach ($c as $key => $value) {
            if (!empty($value->email[0])) {
                $email_list[] = $value->email[0]->emailADDR;
            } else {
                $email_list[] = $value->login;
            }
        }
        return $email_list;
    } //function end
}
if (!function_exists('convertToDatePickerFormat')) {
    /**
     * convert date to date picker format
     * @param  string $date_time mysql date string
     * @return string in datepicker formate (m/d/Y h:i A)
     */
    function convertToDatePickerFormat($date_time)
    {
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $date_time);
        return $date->format('m/d/Y h:i A');
    }
}
if (!function_exists('deleteCampaignThumb')) {
    /**
     * delete campaign thumbnail image from s3
     * @param  object $campaign campaign model
     * @return boolean          true/false
     */
    function deleteCampaignThumb($campaign)
    {
        $path      = getAllDirectoryPathFM();
        $file_name = getEmailTemplateThumbnailName($campaign);
        if (Storage::disk(getDefaultDiskFM())->exists($path['campaign'] . '/thumb/' . $file_name)) {
            return Storage::disk(getDefaultDiskFM())->delete($path['campaign'] . '/thumb/' . $file_name);
        }
    }
}

if (!function_exists('generateLatLngForAddress')) {
    /**
     * generate lat lng for existing address for testing only bunch on 20
     * @param  string  $type single / all address
     * @param bool $for_org true if lat lng is needed for organization
     * @return boolean true/false
     */
    function generateLatLngForAddress($address, $for_org = false)
    {
        if (!empty($address)) {
            if (!empty($address->zip)) {
                $zip_lat_lng = DB::table('ziplatlng')->where('zip', $address->zip)
                    ->orWhere('zip', ltrim($address->zip, "0"))->get()->first();
                if (empty($zip_lat_lng)) {
                    // continue to google api
                } else {
                    $address->lati  = $zip_lat_lng->lat;
                    $address->longi = $zip_lat_lng->lng;
                    $address->save();
                    return true;
                }
            }
            $add_str = '';
            if ($for_org == false) {
                $add_str = $address->addr1 . ', ';
                if (!empty($address->addr2)) {
                    $add_str .= $address->addr2 . ', ';
                }
                $add_str .= $address->city . ', ';
                if (!empty($address->zip)) {
                    $add_str .= $address->state;
                    $add_str .= $address->zip . ', ';
                } else {
                    $add_str .= $address->state . ', ';
                }
                if ($address->cntryID == 228) {
                    //as majority of request will be from this country it will help us reduce query
                    $add_str .= 'United States';
                } else {
                    $country = DB::table('countries')->where('cntryID', $address->cntryID)->get()->first();
                    if (!empty($country)) {
                        $add_str .= $country->cntryName;
                    }
                }
            } else {
                $add_str = $address->orgAddr1 . ', ';
                if (!empty($address->orgAddr2)) {
                    $add_str .= $address->orgAddr2 . ', ';
                }
                $add_str .= $address->orgCity . ', ';
                if (!empty($address->orgZip)) {
                    $add_str .= $address->orgState;
                    $add_str .= $address->orgZip . ', ';
                } else {
                    $add_str .= $address->State . ', ';
                }
                $add_str .= 'United States'; // as currently org does not have any country field
                // if ($address->cntryID == 228) {
                //     //as majority of request will be from this country it will help us reduce query
                //     $add_str .= 'United States';
                // } else {
                //     $country = DB::table('countries')->where('cntryID', $address->cntryID)->get()->first();
                //     if (!empty($country)) {
                //         $add_str .= $country->cntryName;
                //     }
                // }
            }
            $add_str_encode = urlencode($add_str);
            $key            = env('GOOGLE_GEOAPI_KEY');
            $url            = "https://maps.googleapis.com/maps/api/geocode/json?address={$add_str_encode}&key=$key";
            try {
                $resp_json = file_get_contents($url);
            } catch (Exception $ex) {
                return false;
            }
            $resp = json_decode($resp_json, true);
            if ($resp['status'] == 'OK') {
                // get the important data
                $lati              = isset($resp['results'][0]['geometry']['location']['lat']) ? $resp['results'][0]['geometry']['location']['lat'] : "";
                $longi             = isset($resp['results'][0]['geometry']['location']['lng']) ? $resp['results'][0]['geometry']['location']['lng'] : "";
                $formatted_address = isset($resp['results'][0]['formatted_address']) ? $resp['results'][0]['formatted_address'] : "";
                // verify if data is complete
                if ($lati && $longi && $formatted_address) {
                    $address->lati  = $lati;
                    $address->longi = $longi;
                    $address->save();
                    return true;
                } else {
                    return false;
                    // data not found
                }
            } else {
                return false;
                // api issue;
            }
        }
        return false;
    }
}
if (!function_exists('storeLatiLongiFormZip')) {
    /**
     * store lat long for existing address by zip code
     * @return void
     */
    function storeLatiLongiFormZip()
    {
        $person_address = Address::where('lati', '0')->where('longi', '0')->limit(1000)->get();
        foreach ($person_address as $key => $address) {
            if (!empty($address->zip)) {
                $zip_lat_lng = DB::table('ziplatlng')->where('zip', $address->zip)
                    ->orWhere('zip', ltrim($address->zip, "0"))->get()->first();
                if (empty($zip_lat_lng)) {
                    continue;
                } else {
                    $address->lati  = $zip_lat_lng->lat;
                    $address->longi = $zip_lat_lng->lng;
                    $address->save();
                    continue;
                }
            }
        } //loop ends
    }
}

if (!function_exists('has_org_property')) {
    /**
     * check if org property exist and return its value if found
     * @param  object  $org      org object
     * @param  array  $property template category array
     * @return boolean/string   bool if not found otherwise string.
     */
    function has_org_property($org, $property)
    {
        switch ($property['name']) {
            case 'OSN1':
                if (empty($org->OSN1)) {
                    return false;
                } else {
                    return ['[OSN1]' => $org->OSN1];
                }
                break;
            case 'OSN2':
                if (empty($org->OSN2)) {
                    return false;
                } else {
                    return ['[OSN2]' => $org->OSN2];
                }
                break;
            case 'OSN3':
                if (empty($org->OSN3)) {
                    return false;
                } else {
                    return ['[OSN3]' => $org->OSN3];
                }
                break;
            case 'OSN4':
                if (empty($org->OSN4)) {
                    return false;
                } else {
                    return ['[OSN4]' => $org->OSN4];
                }
                break;
            case 'OSN5':
                if (empty($org->OSN5)) {
                    return false;
                } else {
                    return ['[OSN5]' => $org->OSN5];
                }
                break;
            case 'OSN6':
                if (empty($org->OSN6)) {
                    return false;
                } else {
                    return ['[OSN6]' => $org->OSN6];
                }
                break;
            case 'OSN7':
                if (empty($org->OSN7)) {
                    return false;
                } else {
                    return ['[OSN7]' => $org->OSN7];
                }
                break;
            case 'OSN8':
                if (empty($org->OSN8)) {
                    return false;
                } else {
                    return ['[OSN8]' => $org->OSN8];
                }
                break;
            case 'OSN9':
                if (empty($org->OSN9)) {
                    return false;
                } else {
                    return ['[OSN9]' => $org->OSN9];
                }
                break;
            case 'OSN10':
                if (empty($org->OSN10)) {
                    return false;
                } else {
                    return ['[OSN10]' => $org->OSN10];
                }
                break;
            case 'ODN1':
                if (empty($org->ODN1)) {
                    return false;
                } else {
                    return ['[ODN1]' => $org->ODN1];
                }
                break;
            case 'ODN2':
                if (empty($org->ODN2)) {
                    return false;
                } else {
                    return ['[ODN2]' => $org->ODN2];
                }
                break;
            case 'ODN3':
                if (empty($org->ODN3)) {
                    return false;
                } else {
                    return ['[ODN3]' => $org->ODN3];
                }
                break;
            case 'ODN4':
                if (empty($org->ODN4)) {
                    return false;
                } else {
                    return ['[ODN4]' => $org->ODN4];
                }
                break;
            case 'ODN5':
                if (empty($org->ODN5)) {
                    return false;
                } else {
                    return ['[ODN5]' => $org->ODN5];
                }
                break;
            case 'ODN6':
                if (empty($org->ODN6)) {
                    return false;
                } else {
                    return ['[ODN6]' => $org->ODN6];
                }
                break;
            case 'ODN7':
                if (empty($org->ODN7)) {
                    return false;
                } else {
                    return ['[ODN7]' => $org->ODN7];
                }
                break;
            case 'ODN8':
                if (empty($org->ODN8)) {
                    return false;
                } else {
                    return ['[ODN8]' => $org->ODN8];
                }
                break;
            case 'ODN9':
                if (empty($org->ODN9)) {
                    return false;
                } else {
                    return ['[ODN9]' => $org->ODN9];
                }
                break;
            case 'ODN10':
                if (empty($org->ODN10)) {
                    return false;
                } else {
                    return ['[ODN10]' => $org->ODN10];
                }
                break;
            default:
                return false;
                break;
        }
        return true;

    }
}

if (!function_exists('generateEmailListEventArray')) {
    /**
     * get event ids with date and name wise list from event query object.
     * @param  object $event event model object
     * @return array        ids, datewithname, min & max dates
     */
    function generateEmailListEventArray($event)
    {
        $ytd_events_date = [];
        $ids             = [];
        $date_array      = [];
        foreach ($event as $id) {
            array_push($ids, $id->eventID);
            $event_type_name = 'NA';
            if (!empty($id->event_type->etName)) {
                $event_type_name = $id->event_type->etName;
            }
            $date         = $id->eventStartDate->format('Y-m-d');
            $date_array[] = $date;
            $name         = substr($id->eventName, 0, 60);
            if (strlen($name) > 60) {
                $name .= "...";
            }
            $display_date                  = $id->eventStartDate->format(trans('messages.app_params.date_format'));
            $list_name                     = $event_type_name . ': ' . $name . ' - ' . $display_date;
            $ytd_events_date[$id->eventID] = ['date' => $date, 'name' => $list_name];
        }
        $min          = min($date_array);
        $date         = Carbon::createFromFormat('Y-m-d', $min);
        $min          = [];
        $min['year']  = $date->format('Y');
        $min['month'] = $date->format('m');
        $min['day']   = $date->format('d');
        $max          = max($date_array);
        $date         = Carbon::createFromFormat('Y-m-d', $max);
        $max          = [];
        $max['year']  = $date->format('Y');
        $max['month'] = $date->format('m');
        $max['day']   = $date->format('d');
        return [
            'events_with_date' => $ytd_events_date,
            'ids'              => $ids,
            'min_max_date'     => ['min' => $min, 'max' => $max],
        ];
    }
}
if (!function_exists('getJavaScriptDate')) {
    /**
     * return js compactible date from daterangepicker specific date
     * @param  string $date m/d/Y style date
     * @return string       Y-m-d style date
     */
    function getJavaScriptDate($date)
    {
        $date = trim($date);
        $date = Carbon::createFromFormat('m/d/Y', $date);
        return $date->format('Y-m-d');
        $js          = [];
        $js['year']  = $date->format('Y');
        $js['month'] = $date->format('m');
        $js['day']   = $date->format('d');
        return $js;
    }
}

if (!function_exists('replaceAddressWithOrgAddress')) {
    /**
     * replace address string for email builder footer with org address
     * @param  array $item category 6 rows
     * @return array       row data with updated address
     */
    function replaceAddressWithOrgAddress($item)
    {
        $default = 'Your company, Pier 9, San Francisco, CA 12345';
        $start   = '<td contenteditable="true" style="text-align:right">';
        $end     = '</td>';
        $matches = [];
        // for single line address
        if (strpos($item['html'], $default) !== false) {
            $currentPerson = Person::find(auth()->user()->id);
            $org           = $currentPerson->defaultOrg;
            $add_str       = $org->orgAddr1 . ', ';
            if (!empty($org->orgAddr2)) {
                $add_str .= $org->orgAddr2 . ', ';
            }
            $add_str .= $org->orgCity . ', ';
            if (!empty($org->orgZip)) {
                $add_str .= $org->orgState;
                $add_str .= $org->orgZip . ', ';
            } else {
                $add_str .= $org->State . ', ';
            }
            $add_str      = rtrim($add_str, ', ');
            $item['html'] = str_replace($default, $add_str, $item['html']);
            return $item;
        } else if (preg_match("#$start(.*)$end#s", $item['html'], $matches)) {
            // for multiline address first finding if exist then replacing it line by line
            $currentPerson = Person::find(auth()->user()->id);
            $org           = $currentPerson->defaultOrg;
            $add_str       = $org->orgAddr1 . ', ';
            if (!empty($org->orgAddr2)) {
                $add_str .= $org->orgAddr2 . ', ';
            }
            $line1   = $add_str;
            $add_str = $org->orgCity . ', ';
            if (!empty($org->orgZip)) {
                $add_str .= $org->orgState;
                $add_str .= $org->orgZip . ', ';
            } else {
                $add_str .= $org->State . ', ';
            }
            $add_str      = rtrim($add_str, ', ');
            $line2        = $add_str;
            $default      = [];
            $add_str      = [];
            $default[]    = 'Your company, Pier 9,';
            $default[]    = ' San Francisco, CA 12345';
            $add_str[]    = $line1;
            $add_str[]    = $line2;
            $item['html'] = str_replace($default, $add_str, $item['html']);
            return $item;
        } else {
            // return address row as itis
            return $item;
        }
    }
}

if (!function_exists('replaceSocialLinksWithOrgSocialLinks')) {
    /**
     * replace social icon links with actual links, hides if link is not set.
     * @param  array $item category 6 rows
     * @return array       row data with updated links and style
     */
    function replaceSocialLinksWithOrgSocialLinks($item)
    {
        $parsed = new Dom;
        $parsed->load($item['html']);
        //find all anchor tags
        $a             = $parsed->find('a');
        $currentPerson = Person::find(auth()->user()->id);
        $org           = $currentPerson->defaultOrg;
        foreach ($a as $key => $value) {
            $tag = $value->getTag();
            //get anchor tag html attributes
            $class = $tag->getAttribute('class');
            $class = $class['value'];
            switch ($class) {
                case 'instagram':
                    // as this link is not yet available in db we can hide it
                    $style = $tag->getAttribute('style');
                    $style = $style['value'];
                    $style = str_replace('inline-block', 'none', $style);
                    $tag->setAttribute('style', $style);
                    break;
                case 'pinterest':
                    // as this link is not yet available in db we can hide it
                    $style = $tag->getAttribute('style');
                    $style = $style['value'];
                    $style = str_replace('inline-block', 'none', $style);
                    $tag->setAttribute('style', $style);
                    break;
                case 'google-plus':
                    //check if link exist and set it into href tag otherwise hide it.
                    if (!empty($org->googleURL)) {
                        $tag->setAttribute('href', $org->googleURL);
                    } else {
                        $style = $tag->getAttribute('style');
                        $style = $style['value'];
                        $style = str_replace('inline-block', 'none', $style);
                        $tag->setAttribute('style', $style);
                    }
                    break;
                case 'facebook':
                    //check if link exist and set it into href tag otherwise hide it.
                    if (!empty($org->facebookURL)) {
                        $tag->setAttribute('href', $org->facebookURL);
                    } else {
                        $style = $tag->getAttribute('style');
                        $style = $style['value'];
                        $style = str_replace('inline-block', 'none', $style);
                        $tag->setAttribute('style', $style);
                    }
                    break;
                case 'twitter':
                    //check if link exist and set it into href tag otherwise hide it.
                    if (!empty($org->orgHandle)) {
                        $tag->setAttribute('href', 'https://twitter.com/' . str_replace("@", '', $org->orgHandle));
                    } else {
                        $style = $tag->getAttribute('style');
                        $style = $style['value'];
                        $style = str_replace('inline-block', 'none', $style);
                        $tag->setAttribute('style', $style);
                    }
                    break;
                case 'linkedin':
                    //check if link exist and set it into href tag otherwise hide it.
                    if (!empty($org->linkedinURL)) {
                        $tag->setAttribute('href', $org->linkedinURL);
                    } else {
                        $style = $tag->getAttribute('style');
                        $style = $style['value'];
                        $style = str_replace('inline-block', 'none', $style);
                        $tag->setAttribute('style', $style);
                    }
                    break;
                case 'youtube':
                    // as this link is not yet available in db we can hide it
                    $style = $tag->getAttribute('style');
                    $style = $style['value'];
                    $style = str_replace('inline-block', 'none', $style);
                    $tag->setAttribute('style', $style);
                    break;
                case 'skype':
                    // as this link is not yet available in db we can hide it
                    $style = $tag->getAttribute('style');
                    $style = $style['value'];
                    $style = str_replace('inline-block', 'none', $style);
                    $tag->setAttribute('style', $style);
                    break;
            } //switch end
        } //foreach end
        //save output
        ob_start();
        echo $parsed;
        $str          = ob_get_clean();
        $item['html'] = $str;
        return $item;
    }
}

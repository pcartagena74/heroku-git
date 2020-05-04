<?php

namespace App\Http\Controllers;

use App\Campaign;
use App\Jobs\SendCampaignEmail;
use App\Models\EmailCampaignTemplateBlock;
use App\Models\EmailQueue;
use App\Org;
use App\Person;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mail;
use Spatie\Browsershot\Browsershot;
use Spatie\Image\Manipulations;
use Validator;

class CampaignController extends Controller
{
    protected $currentPerson;
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['mailgunWebhook']]);
        $this->currentPerson = Person::find(auth()->id());
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //->with('emails', 'emails.urls', 'email_count', 'emails.url_count')

        $campaigns = Campaign::where('orgID', $this->currentPerson->defaultOrgID)
            ->with('emails.click_count')
            ->with('mailgun')
            ->withCount('emails', 'urls')
            ->orderBy('campaignID', 'DESC')
            ->get();
        return view('v1.auth_pages.campaigns.campaigns', compact('campaigns'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->currentPerson = Person::find(auth()->id());
        $org                 = Org::find($this->currentPerson->defaultOrgID);
        $campaign_name       = 'Untitled Campaign ' . date('Y-m-d H:i:s', time());
        $list_dp             = $this->generateEmailList();

        // return view('v1.auth_pages.campaigns.email_builder', compact('org'));
        return view('v1.auth_pages.campaigns.add-edit_campaign', compact('org', 'campaign_name', 'list_dp'));
    }
    private function storeCampaignEmailTemplate($request, $new_campaign = false)
    {
        $campaign            = new Campaign;
        $this->currentPerson = Person::find(auth()->id());
        $campaign->orgID     = $this->currentPerson->defaultOrgID;
        $campaign_name       = 'Untitled Campaign ' . date('Y-m-d H:i:s', time());
        $campaign->title     = $request->input('name');
        if ($new_campaign) {
            $campaign->title = $campaign_name;
        }
        $campaign->fromName    = $request->input('from_name');
        $campaign->fromEmail   = $request->input('from_email');
        $campaign->replyEmail  = $request->input('from_email');
        $campaign->subject     = $request->input('subject');
        $campaign->preheader   = $request->input('preheader');
        $campaign->emailListID = $request->input('email_list');
        $campaign->creatorID   = $this->currentPerson->personID;
        $campaign->updaterID   = $this->currentPerson->personID;
        $campaign->content     = $request->input('content');
        $content               = $request->input('contentArr');
        if (empty(request()->input('subject'))) {
            $campaign->subject = $campaign->title;
        }
        $campaign->save();
        $raw_html = '';
        foreach ($content as $key => $value) {
            if (isset($value['id'])) {
                EmailCampaignTemplateBlock::create([
                    'campaign_id' => $campaign->campaignID,
                    'block_id'    => $value['id'],
                    'content'     => $value['content'],
                ]);
                $raw_html .= $value['content'];
            } else {
                EmailCampaignTemplateBlock::create([
                    'campaign_id' => $campaign->campaignID,
                    'block_id'    => 0,
                    'content'     => $value['content'],
                ]);
                $raw_html .= $value['content'];
            }
        }
        generateEmailTemplateThumbnail($html = $campaign->content, $campaign = $campaign, $currentPerson = $this->currentPerson);
        return $campaign;
    }

    private function updateCampaignEmailTemplate($campaign, $request)
    {
        $campaign->title       = $request->input('name');
        $campaign->fromName    = $request->input('from_name');
        $campaign->fromEmail   = $request->input('from_email');
        $campaign->replyEmail  = $request->input('from_email');
        $campaign->subject     = $request->input('subject');
        $campaign->preheader   = $request->input('preheader');
        $campaign->emailListID = $request->input('email_list');
        $campaign->content     = $request->input('content');
        $content               = $request->input('contentArr');
        if (empty($campaign->subject)) {
            $campaign->subject = $campaign->title;
        }
        $campaign->updaterID = $this->currentPerson->personID;
        $campaign->save();
        $content  = $request->input('contentArr');
        $raw_html = '';
        if (!empty($content) && count($content) > 0) {
            EmailCampaignTemplateBlock::where('campaign_id', $campaign->campaignID)->delete();
            foreach ($content as $key => $value) {
                if (isset($value['id'])) {
                    $raw_html .= $value['content'];
                    EmailCampaignTemplateBlock::create([
                        'campaign_id' => $campaign->campaignID,
                        'block_id'    => $value['id'],
                        'content'     => $value['content'],
                    ]);
                }
            }
        }
        generateEmailTemplateThumbnail($html = $raw_html, $campaign = $campaign, $currentPerson = $this->currentPerson);
    }
    /**
     * create campiagn and store template
     * @param  Request $request
     * @return json
     */
    public function storeEmailTemplate(Request $request)
    {

        $content = $request->input('contentArr');
        if (empty($content)) {
            return response()->json(['success' => false, 'errors' => ['Template Empty please some elements']]);
        }
        $campaign = $this->storeCampaignEmailTemplate($request);
        // generateEmailTemplateThumbnail($html = $campaign->content, $campaign = $content, $currentPerson = $this->currentPerson);
        return response()->json(['success' => true, 'message' => 'Template Saved', 'redirect_url' => url('campaign', $campaign->campaignID)]);
    }

    /**
     * update campaign
     * @param  Request $request
     * @return json
     */
    public function updateEmailTemplate(Request $request)
    {
        $campaign_id      = $request->input('id');
        $campaign         = Campaign::find($campaign_id);
        $current_datetime = Carbon::now()->toDateTimeString();
        if (empty($campaign)) {
            return response()->json(['success' => false, 'errors' => ['Campaign not found!']]);
        }
        $content = $request->input('contentArr');
        if (empty($content)) {
            return response()->json(['success' => false, 'errors' => ['Template Empty please some elements']]);
        }
        if (!empty($campaign->sendDate)) {
            if (!empty($campaign->scheduleDate) && $campaign->scheduleDate > $current_datetime) {
                EmailQueue::where('campaign_id', $campaign->campaignID)->delete();
                $campaign->scheduleDate = null;
                $campaign->sendDate     = null;
                $campaign->save();
                updateCampaignEmailTemplate($campaign, $request);
            } else {
                $campaign = $this->storeCampaignEmailTemplate($request, true);
                request()->session()->flash('alert-success', trans('messages.messages.campaign_created_from_existing'));
                return response()->json(['success' => true, 'message' => 'Template Updated', 'redirect' => url('campaign', $campaign->campaignID, 'edit')]);
            }
        } else {
            updateCampaignEmailTemplate($campaign, $request);
            return response()->json(['success' => true, 'message' => 'Template Updated']);
        }
    }

    /**
     * get email template list for popup with pagination
     * @param  Request $request
     * @return json with pagination html
     */
    public function getEmailTemplates(Request $request)
    {
        $campaigns = Campaign::where('orgID', $this->currentPerson->defaultOrgID)
            ->orWhere('orgID', 1)
            ->orderBy('orgID', 'ASC')
            ->orderBy('campaignID', 'desc')->paginate(10);
        $pages = $campaigns->links();
        foreach ($campaigns as $key => $value) {
            $campaigns[$key]->thumbnail = getEmailTemplateThumbnailURL($value);
        }
        return response()->json(['success' => true, 'list' => $campaigns, 'pages' => $pages->toHtml()]);
    }

    /**
     * store email template html for preview
     * @param  Request $request
     * @return json
     */
    public function storeEmailTemplateForPreview(Request $request)
    {
        $html      = $request->input('html');
        $html      = replaceUserDataInEmailTemplate($email = null, $campaign = null, $for_preview = true, $raw_html = $html);
        $file_name = Str::random(40) . '.html';
        $tmp_path  = Storage::disk('local')->put($file_name,
            view('v1.auth_pages.campaigns.preview_email_template')
                ->with(['html' => $html])->render());
        // Build pack is needed in heroku to run this
        // also below argunment are not safe but are requied to run this library on heroku
        //https://github.com/jontewks/puppeteer-heroku-buildpack and node
        $img = Browsershot::html($html)
            ->fullPage()
            ->fit(Manipulations::FIT_CONTAIN, 400, 400)
            ->addChromiumArguments(['no-sandbox', 'disable-setuid-sandbox'])
            ->save(Storage::disk('local')->path($file_name . '.png'));
        return response()->json(['success' => true, 'preview_url' => url('preview-email-template', $file_name)]);
    }

    /**
     * preview saved template
     * @param  Request $request
     * @param  string  $filename
     * @return json
     */
    public function previewEmailTemplate(Request $request, $filename)
    {
        if (Storage::disk('local')->exists($filename)) {
            return Storage::disk('local')->get($filename);
        } else {
            abort(404);
        }
    }

    /**
     * show thumbnail image
     * @param  Request $request
     * @param  string  $filename
     * @return json
     */
    public function getemailTemplateThumbnailImage(Request $request, $filename)
    {
        if (Storage::disk('local')->exists($filename)) {
            $path    = Storage::disk('local')->path($filename);
            $headers = ["Content-Type" => 'image/png'];
            return response()->file($path, $headers);
        } else {
            abort(404);
        }
    }

    /**
     * get only blocks of email template used after loading  popup
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function getEmailTemplateBlocks(Request $request)
    {
        $campaign = Campaign::find($request->input('id'));
        if (empty($campaign)) {
            return response()->json(['success' => false, 'message' => 'Template not found!']);
        }
        return response()->json(['success' => true, 'blocks' => $campaign->template_blocks]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //dd(request()->all());
        $this->currentPerson = Person::find(auth()->id());
        $test_emails         = [];

        // 1. store the campaign object
        $c             = new Campaign;
        $c->orgID      = $this->currentPerson->defaultOrgID;
        $c->title      = 'Campaign';
        $c->fromName   = request()->input('from_name');
        $c->fromEmail  = request()->input('from_email');
        $c->replyEmail = request()->input('from_email');
        $c->subject    = request()->input('subject');
        $c->preheader  = request()->input('preheader');
        $c->content    = request()->input('content');
        $c->creatorID  = $this->currentPerson->personID;
        $c->updaterID  = $this->currentPerson->personID;
        $c->save();

        // 2. Prep for any test emails and send if 'Send Test Message"
        $note = request()->input('note');
        for ($i = 1; $i <= 5; $i++) {
            $e = request()->input('email' . $i);
            if (!empty($e)) {
                request()->session()->flash('alert-info', "Test message(s) sent.");
                array_push($test_emails, $e);
                /**
                 * below is to set queue for email its a sample for bulk emailing
                 */
                // dispatch(new \App\Jobs\SendEmailJob($campaigns[0]));
                Mail::send(
                    'v1.auth_pages.campaigns.generic_campaign_email',
                    ['content' => $c->content],
                    function ($message) use ($c, $e) {
                        $message->from($c->fromEmail, $c->fromName);
                        $message->sender($c->fromEmail, $c->fromName);
                        $message->to($e, $name = null);
                        $message->subject($c->subject);
                        // Create a custom header that we can later retrieve
                        //$message->getHeaders()->addTextHeader('X-Model-ID',$model->id);
                    }
                );
            }
        }
        return redirect(env('APP_URL') . "/campaigns");
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $campaign            = Campaign::findOrFail($id);
        $this->currentPerson = Person::find(auth()->id());
        $org                 = Org::find($this->currentPerson->defaultOrgID);
        $list_dp             = $this->generateEmailList();
        $current_datetime    = Carbon::now()->toDateTimeString();
        return view('v1.auth_pages.campaigns.add-edit_campaign', compact('org', 'campaign', 'list_dp', 'current_datetime'));
    }

    public function show_campaign(Campaign $campaign)
    {
        $content = $campaign->content;
        return view('v1.auth_pages.campaigns.generic_campaign_email', compact('content'));
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Campaign $campaign)
    {
        $this->currentPerson = Person::find(auth()->id());
        $org                 = Org::find($this->currentPerson->defaultOrgID);
        $list_dp             = $this->generateEmailList();
        $current_datetime    = Carbon::now()->toDateTimeString();
        return view('v1.auth_pages.campaigns.add-edit_campaign', compact('org', 'campaign', 'list_dp', 'current_datetime'));

        // $this->currentPerson = Person::find(auth()->id());
        // $org                 = Org::find($this->currentPerson->defaultOrgID);
        // return view('v1.auth_pages.campaigns.email_builder', compact('campaign', 'org'));
    }

    private function generateEmailList()
    {
        $list_dp     = [];
        $list_dp[''] = 'Select Mailing List';
        $defaults    = getDefaultEmailList($this->currentPerson, $for_select = true);
        $lists       = getEmailList($this->currentPerson, $for_select = true);
        foreach ($defaults as $key => $value) {
            $list_dp[$value['id']] = $value['name'] . '(' . $value['count'] . ')';
        }
        foreach ($lists as $key => $value) {
            $list_dp[$value['id']] = $value['name'] . '(' . $value['count'] . ')';
        }
        return $list_dp;
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * test method to check variable parsing
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function sendTestEmail(Request $request)
    {
        $currentPerson = $this->currentPerson;
        $email_1       = $request->input('email1');
        $email_2       = $request->input('email2');
        $email_3       = $request->input('email3');
        $email_4       = $request->input('email4');
        $email_5       = $request->input('email5');
        $valid_email   = [];
        if (!empty($email_1) && filter_var($email_1, FILTER_VALIDATE_EMAIL)) {
            $valid_email[] = $email_1;
        }
        if (!empty($email_2) && filter_var($email_2, FILTER_VALIDATE_EMAIL)) {
            $valid_email[] = $email_2;
        }
        if (!empty($email_3) && filter_var($email_3, FILTER_VALIDATE_EMAIL)) {
            $valid_email[] = $email_3;
        }
        if (!empty($email_4) && filter_var($email_4, FILTER_VALIDATE_EMAIL)) {
            $valid_email[] = $email_4;
        }
        if (!empty($email_5) && filter_var($email_5, FILTER_VALIDATE_EMAIL)) {
            $valid_email[] = $email_5;
        }
        if (count($valid_email) == 0) {
            return response()->json(['success' => false, 'message' => trans('messages.errors.no_valid_email')]);
        }
        $html = $request->input('html');
        if (empty($html)) {
            return response()->json(['success' => false, 'message' => trans('messages.errors.empty_template')]);
        }
        $note     = $request->input('note');
        $campaign = $request->input('campaign');
        $subject  = 'Test Email';
        if (!empty($campaign)) {
            $campaign = Campaign::where('campaignID', $campaign)->get()->first();
            if (!empty($campaign)) {
                $subject = 'Test : ' . $campaign->subject;
            }
        }
        $html = replaceUserDataInEmailTemplate(null, null, true, $html);
        $mail = Mail::send('v1.auth_pages.campaigns.email_template_with_note', ['html' => $html, 'note' => $note],
            function ($message) use ($currentPerson, $valid_email, $subject) {
                $message->from($currentPerson->login);
                $message->sender($currentPerson->login);
                $message->to($valid_email);
                $message->subject($subject);
            }
        );
        return response()->json(['success' => true, 'message' => trans('messages.messages.test_email_sent')]);
    }

    public function copy(Request $request, $campaign_id)
    {
        $campaign            = Campaign::findOrFail($campaign_id);
        $campaign_name_count = Campaign::where('orgID', $this->currentPerson->defaultOrgID)
            ->where('title', 'LIKE', "$campaign->title%")
            ->get()->count();
        $new_campaign = $campaign->replicate();
        if ($campaign_name_count == 0) {
            $new_campaign->title = $campaign->title . '-Copy';
        } else {
            if (strpos($campaign->title, '-Copy')) {
                $new_name = substr($campaign->title, 0, strpos($campaign->title, '-Copy'));
            }
        }
        if (strlen($new_campaign->title) > 55) {
            $new_campaign->title = $campaign->title;
        }
        $date_time                  = Carbon::now()->toDateTimeString();
        $new_campaign->title        = 'Untitled Campaign ' . $date_time;
        $new_campaign->sendDate     = null;
        $new_campaign->scheduleDate = null;
        $new_campaign->save();
        $email_block = EmailCampaignTemplateBlock::where('campaign_id', $campaign->campaignID)->get();
        $raw_html    = '';
        foreach ($email_block as $key => $value) {
            $new_block              = $value->replicate();
            $new_block->campaign_id = $new_campaign->campaignID;
            $new_block->save();
            $raw_html .= $new_block->content;
        }
        generateEmailTemplateThumbnail($html = $raw_html, $campaign = $new_campaign, $currentPerson = $this->currentPerson);
        request()->session()->flash('alert-success', trans('messages.messages.campaign_copied_successfully'));
        return redirect(url('campaign', $new_campaign->campaignID, 'edit'));
    }

    public function sendCampaign(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schedule'   => 'nullable|date',
            'campaign'   => 'required|exists:org-campaign,campaignID',
            'from_name'  => 'required|max:255|min:3',
            'from_email' => 'required|email',
            'subject'    => 'required|max:255|min:3',
            'preheader'  => 'nullable|max:255|min:3',
            'email_list' => 'required|exists:email-list,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'validation_error' => $validator->errors()]);
        }
        $schedule              = $request->input('schedule');
        $campaign_id           = $request->input('campaign');
        $campaign              = Campaign::where('campaignID', $campaign_id)->get()->first();
        $campaign->fromEmail   = $request->input('from_email');
        $campaign->subject     = $request->input('subject');
        $campaign->preheader   = $request->input('preheader');
        $campaign->emailListID = $request->input('email_list');
        if (!empty($campaign->scheduleDate) && empty($schedule)) {
            $campaign->scheduleDate = null;
        }
        $campaign->save();
        $date_schedule = '';
        if (!empty($schedule)) {
            $date_time              = explode(' ', $schedule, 2);
            $date                   = date("Y-m-d", strtotime($date_time[0]));
            $time                   = date("H:i", strtotime($date_time[1]));
            $dt                     = $date . ' ' . $time;
            $date                   = Carbon::createFromFormat('Y-m-d H:i', $dt);
            $date_schedule          = $date->format('Y-m-d H:i:s');
            $campaign->scheduleDate = $date_schedule;
            $campaign->save();
        }
        $list_id      = $campaign->emailListID;
        $org_id       = $this->currentPerson->defaultOrgID;
        $contacts     = getEmailListContact($list_id, $org_id);
        $insert_queue = [];
        EmailQueue::where('campaign_id', $campaign_id)->delete();
        foreach ($contacts as $key => $value) {
            $value     = 'mufaddal@systango.com'; /// for testing only
            $to_insert = ['campaign_id' => $campaign_id, 'org_id' => $org_id, 'email_id' => $value];
            if (!empty($schedule)) {
                $to_insert['scheduled_datetime'] = $date_schedule;
            }
            $insert_queue[] = $to_insert;
        }
        $var = EmailQueue::insert($insert_queue);

        if ($var) {
            $campaign->sendDate = Carbon::now(); // remove for testing only
            $campaign->save();
            dispatch(new SendCampaignEmail());
            request()->session()->flash('alert-success', trans('messages.messages.campaign_send'));
            return response()->json(['success' => true, 'message' => trans('messages.messages.campaign_send'), 'redirect' => url('campaign', $campaign->campaignID)]);
        }
    }
    public function deleteCampaign(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'campaign' => 'required|exists:org-campaign,campaignID',
        ]);
        if ($validator->fails()) {
            $error = $validator->errors();
            $error = $error->all();
            return response()->json(['success' => false, 'message' => $error[0]]);
        }
        $campaign_id = $request->input('campaign');

        $campaign = Campaign::where('campaignID', $campaign_id)->get()->first();
        if (empty($campaign)) {
            return response()->json(['success' => false, 'message' => trans('messages.errors.campaign_not_exist')]);
        }
        if (empty($campaign->sendDate)) {
            EmailCampaignTemplateBlock::where('campaign_id', $campaign->campaignID)->delete();
            $campaign->delete();
            return response()->json(['success' => true, 'message' => trans('messages.messages.campaign_deleted')]);
        } else if (!empty($campaign->sendDate) && (empty($campaign->scheduleDate) || !empty($campaign->scheduleDate))) {
            EmailCampaignTemplateBlock::where('campaign_id', $campaign->campaignID)->delete();
            EmailQueue::where('campaign_id', $campaign->campaignID)->delete();
            $campaign->delete();
            return response()->json(['success' => true, 'message' => trans('messages.messages.campaign_deleted')]);
        }
    }
    public function mailgunWebhook(Request $request)
    {
        $response   = $request->all();
        $event      = $response['event-data']['event'];
        $message_id = $response['event-data']['message']['headers']['message-id'];
        $email_db   = EmailQueue::where(['message_id' => $message_id])->get()->first();
        switch ($event) {
            case 'delivered':
                $email_db->delivered = 1;
                $email_db->save();
                break;
            case 'clicked':
                $email_db->click = 1;
                $email_db->save();
                break;
            case 'opened':
                $email_db->open = 1;
                $email_db->save();
                break;

            default:
                # code...
                break;
        }
        // Log::info('User failed to login.', ['id' => $event, 'message_id' => $message_id]);
        return response()->json(['success' => true, 'message' => trans('messages.messages.campaign_deleted')]);
    }
}

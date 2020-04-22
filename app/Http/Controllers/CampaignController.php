<?php

namespace App\Http\Controllers;

use App\Campaign;
use App\Models\EmailCampaignTemplateBlock;
use App\Org;
use App\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mail;
use Spatie\Browsershot\Browsershot;
use Spatie\Image\Manipulations;

class CampaignController extends Controller
{
    protected $currentPerson;
    public function __construct()
    {
        $this->middleware('auth');
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
        $campaign_name       = 'Untitled Template ' . date('Y-m-d H:i:s', time());
        // return view('v1.auth_pages.campaigns.email_builder', compact('org'));
        return view('v1.auth_pages.campaigns.add-edit_campaign', compact('org', 'campaign_name'));
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
        $c                   = new Campaign;
        $this->currentPerson = Person::find(auth()->id());
        $c->orgID            = $this->currentPerson->defaultOrgID;
        $c->title            = $request->input('name');
        $c->fromName         = $request->input('from_name');
        $c->fromEmail        = $request->input('from_email');
        $c->replyEmail       = $request->input('from_email');
        $c->subject          = request()->input('subject');
        $c->preheader        = request()->input('preheader');
        $c->creatorID        = $this->currentPerson->personID;
        $c->updaterID        = $this->currentPerson->personID;
        if (empty(request()->input('subject'))) {
            $c->subject = $c->title;
        }
        $c->save();
        $raw_html = '';
        foreach ($content as $key => $value) {
            if (isset($value['id'])) {
                EmailCampaignTemplateBlock::create([
                    'campaign_id' => $c->campaignID,
                    'block_id'    => $value['id'],
                    'content'     => $value['content'],
                ]);
                $raw_html .= $value['content'];
            } else {
                EmailCampaignTemplateBlock::create([
                    'campaign_id' => $c->campaignID,
                    'block_id'    => 0,
                    'content'     => $value['content'],
                ]);
                $raw_html .= $value['content'];
            }
        }
        generateEmailTemplateThumbnail($html = $raw_html, $campaign = $c, $currentPerson = $this->currentPerson);
        return response()->json(['success' => true, 'message' => 'Template Saved', 'redirect_url' => url('campaign', $c->campaignID)]);
    }

    /**
     * update campaign
     * @param  Request $request
     * @return json
     */
    public function updateEmailTemplate(Request $request)
    {
        $campaign_id = $request->input('id');
        $campaign    = Campaign::find($campaign_id);
        if (empty($campaign)) {
            return response()->json(['success' => false, 'errors' => ['Campaign not found!']]);
        }
        $content = $request->input('contentArr');
        if (empty($content)) {
            return response()->json(['success' => false, 'errors' => ['Template Empty please some elements']]);
        }

        $campaign->title      = $request->input('name');
        $campaign->fromName   = $request->input('from_name');
        $campaign->fromEmail  = $request->input('from_email');
        $campaign->replyEmail = $request->input('from_email');
        $campaign->subject    = $request->input('subject');
        $campaign->preheader  = request()->input('preheader');
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
        return response()->json(['success' => true, 'message' => 'Template Updated']);
    }

    /**
     * get email template list for popup with pagination
     * @param  Request $request
     * @return json with pagination html
     */
    public function getEmailTemplates(Request $request)
    {
        $campaigns = Campaign::where('orgID', $this->currentPerson->defaultOrgID)->orderBy('campaignID', 'desc')->paginate(10);
        $pages     = $campaigns->links();
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
        //https://github.com/jontewks/puppeteer-heroku-buildpack
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
        return view('v1.auth_pages.campaigns.add-edit_campaign', compact('org', 'campaign'));
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
        return view('v1.auth_pages.campaigns.add-edit_campaign', compact('org', 'campaign'));

        // $this->currentPerson = Person::find(auth()->id());
        // $org                 = Org::find($this->currentPerson->defaultOrgID);
        // return view('v1.auth_pages.campaigns.email_builder', compact('campaign', 'org'));
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
        $email = $request->input('email');
        if (empty($email)) {
            $email = 'mufaddal@systango.com';
        }
        $id       = 69;
        $campaign = Campaign::where('campaignID', $id)->get()->first();
        $html     = replaceUserDataInEmailTemplate($email, $campaign);
        $html     = view('v1.auth_pages.campaigns.preview_email_template')
            ->with(['html' => $html])->render();
        $mail = Mail::send(
            'v1.auth_pages.campaigns.preview_email_template',
            ['html' => $html],
            function ($message) use ($campaign, $email) {
                $message->from($campaign->fromEmail, $campaign->fromName);
                $message->sender($campaign->fromEmail, $campaign->fromName);
                $message->to($email, $name = null);
                $message->subject($campaign->subject);
                // Create a custom header that we can later retrieve
                //$message->getHeaders()->addTextHeader('X-Model-ID',$model->id);
            }
        );
        dd($mail);
    }

    public function copy(Request $request, $campaign_id)
    {
        $campaign            = Campaign::findOrFail($campaign_id);
        $campaign_name_count = Campaign::where('orgID', $this->currentPerson->defaultOrgID)
            ->where('title', 'LIKE', "$campaign->title%")
            ->get()->count();
        // dd($campaign_name_count->toSql(), $campaign_name_count->getBindings());
        $new_campaign = $campaign->replicate();
        if ($campaign_name_count == 0) {
            $new_campaign->title = $campaign->title . ' -Copy';
        } else {
            $new_campaign->title = $campaign->title . ' -Copy ' . ++$campaign_name_count;
        }
        if (count($new_campaign->title) > 55) {
            $new_campaign->title = $campaign->title;
        }
        $new_campaign->save();
        $email_block = EmailCampaignTemplateBlock::where('campaign_id', $campaign->campaignID)->get();
        foreach ($email_block as $key => $value) {
            $new_block              = $value->replicate();
            $new_block->campaign_id = $new_campaign->campaignID;
            $new_block->save();
        }
        request()->session()->flash('alert-success', trans('messages.messages.campaign_copied_successfully'));
        return redirect(url('campaign', $new_campaign->campaignID, 'edit'));
    }
}

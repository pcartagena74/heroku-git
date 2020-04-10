<?php

namespace App\Http\Controllers;

use App\Campaign;
use App\Models\EmailCampaignTemplateBlock;
use App\Org;
use App\Person;
use Illuminate\Http\Request;
use Mail;

class CampaignController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->currentPerson = Person::find(auth()->id());

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
        // return view('v1.auth_pages.campaigns.email_builder', compact('org'));
        return view('v1.auth_pages.campaigns.email_builder', compact('org'));
    }
    public function storeEmailTemplate(Request $request)
    {
        $c                   = new Campaign;
        $this->currentPerson = Person::find(auth()->id());
        $c->orgID            = $this->currentPerson->defaultOrgID;
        $c->title            = $request->input('name');
        $c->fromName         = $request->input('from_name');
        $c->fromEmail        = $request->input('from_email');
        $c->replyEmail       = $request->input('from_email');
        // $c->subject          = request()->input('subject');
        $c->subject   = 'subject';
        $c->preheader = request()->input('preheader');
        $c->creatorID = $this->currentPerson->personID;
        $c->updaterID = $this->currentPerson->personID;
        $c->save();
        $content = $request->input('contentArr');
        foreach ($content as $key => $value) {
            if (isset($value['id'])) {
                EmailCampaignTemplateBlock::create([
                    'campaign_id' => $c->campaignID,
                    'block_id'    => $value['id'],
                    'content'     => $value['content'],
                ]);
            }
        }
        return response()->json(['success' => true, 'message' => 'Template Saved']);
    }

    public function loadEmailTemplate()
    {

    }
    public function createTemplatePreview(Request $request)
    {
        $html     = $request->input('html');
        $todayh   = getdate();
        $filename = "email-editor-" . $todayh['seconds'] . $todayh['minutes'] . $todayh['hours'] . $todayh['mday'] . $todayh['mon'] . $todayh['year'];

        $newHtmlFilename = EXPORTS_DIRECTORY . $filename . '.html';
        $zipFilename     = EXPORTS_DIRECTORY . $filename . '.zip';
        $zipFileUrl      = EXPORTS_URL . $filename . '.zip';
        $htmlFileUrl     = EXPORTS_URL . $filename . '.html';

        //read email template
        $templateContent = file_get_contents("template.html", true);

        //create new document
        $new_content = $html;

        //view in browser link
        $new_content = str_replace('#view_web', $htmlFileUrl, $new_content);

        $content = str_replace('[email-body]', $new_content, $templateContent);
        $fp      = fopen($newHtmlFilename, "wb");
        fwrite($fp, $content);
        fclose($fp);

        //create zip document
        // $zip = new ZipArchive();

        // $zip->open($zipFilename, ZipArchive::CREATE);
        // $zip->addFile($newHtmlFilename, 'index.html');
        // $zip->close();
        //remove html file
        //unlink($newHtmlFilename);
        $zipFileUrl              = url('/');
        $response                = array();
        $response['code']        = 0;
        $response['url']         = $zipFileUrl;
        $response['preview_url'] = $htmlFileUrl;
        $response['html']        = $new_content;

        return $request->response;
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
        $this->currentPerson = Person::find(auth()->id());
        $org                 = Org::find($this->currentPerson->defaultOrgID);
        // return view('v1.auth_pages.campaigns.email_builder', compact('org'));
        return view('v1.auth_pages.campaigns.email_builder', compact('org'));
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
        return view('v1.auth_pages.campaigns.email_builder', compact('campaign', 'org'));
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

}

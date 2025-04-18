<?php

namespace App\Http\Controllers;

use App\Jobs\SendCampaignEmail;
use App\Models\Campaign;
use App\Models\EmailCampaignLink;
use App\Models\EmailCampaignTemplateBlock;
use App\Models\EmailQueue;
use App\Models\EmailQueueLink;
use App\Models\Org;
use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Mail;
use Spatie\Browsershot\Browsershot;
use Spatie\Image\Manipulations;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Validator;

class CampaignController extends Controller
{
    protected $currentPerson;

    public function __construct()
    {
        $this->middleware('auth', ['except' => ['mailgunWebhook']]);
        $this->currentPerson = Person::find(auth()->id());
    }

    public function listCampaign(Request $request)
    {
        $campaigns = Campaign::where('orgID', $this->currentPerson->defaultOrgID)
            ->with('emails.click_count')
            ->with('mailgun')
            ->withCount('emails', 'urls')
            ->orderBy('campaignID', 'DESC');
        // ->whereNull('archived_date');
        //check if filter is beign applied
        $filter = $request->input('columns');
        foreach ($filter as $key => $value) {
            if ($value['searchable'] == true && $value['data'] == 'status') {
                // dd($value);
                if (empty($value['search']['value'])) {
                    $campaigns->whereNull('archived_date');
                } else {
                    if ($value['search']['value'] == 'sent' || $value['search']['value'] == 'draft') {
                        $campaigns->whereNull('archived_date');
                    }
                }
            }
        }
        // dd($filter);
        // $request->merge($filter);

        $collection = datatables()->of($campaigns);
        $collection->filterColumn('Status', function ($query, $keyword) {
            switch ($keyword) {
                case 'draft':
                    $query->whereNull('sendDate');
                    $query->whereNull('archived_date');
                    break;
                case 'sent':
                    $query->whereNotNull('sendDate');
                    $query->whereNull('archived_date');
                    break;
                case 'archive':
                    $query->whereNotNull('archived_date');
                    break;
            }
        });

        $collection->addColumn('thumb', function ($c) {
            $str = '<a href="'.url('campaign', [$c->campaignID, 'edit']).'"><img class="img-thumbnail" height="70px" src="'.getEmailTemplateThumbnailURL($c).'" width="70px" /></a>';

            return $str;
        });
        $collection->addColumn('status', function ($c) {
            $status = '';
            if ($c->sendDate === null) {
                $date = $c->createDate->format(trans('messages.app_params.datetime_format'));
                $status = trans('messages.fields.camp_status_draft', ['date' => $date]);
            } else {
                $date = $c->sendDate->format(trans('messages.app_params.datetime_format'));
                $status = trans('messages.fields.camp_status_sent', ['date' => $date]);
            }

            return (string) $status;
        });
        $collection->addColumn('total_sent', function ($c) {
            if ($c->mailgun) {
                return $c->mailgun->total_sent;
            }

            return 0;
        });
        $collection->addColumn('open', function ($c) {
            if ($c->mailgun) {
                return $c->mailgun->open;
            }

            return 0;
        });
        $collection->addColumn('click', function ($c) {
            if ($c->mailgun) {
                return $c->mailgun->click;
            }

            return 0;
        });
        $collection->addColumn('status', function ($c) {
            $status = '';
            if ($c->sendDate === null) {
                $date = $c->createDate->format(trans('messages.app_params.datetime_format'));
                $status = trans('messages.fields.camp_status_draft', ['date' => $date]);
            } else {
                $date = $c->sendDate->format(trans('messages.app_params.datetime_format'));
                $status = trans('messages.fields.camp_status_sent', ['date' => $date]);
            }

            return (string) $status;
        });
        $collection->addColumn('action', function ($c) {
            $str = '';
            if ($c->sendDate === null) {
                $str .= '<a class="btn btn-primary btn-sm" href="'.url('campaign', [$c->campaignID, 'edit']).'" title="'.trans('messages.buttons.common_edit').'">
                        <i aria-hidden="true" class="fa fa-edit">
                        </i>
                    </a>';
            } else {
                $str .= '<a class="btn btn-primary btn-sm" href="'.url('campaign', [$c->campaignID, 'edit']).'" title="'.trans('messages.buttons.common_view').'">
                        <i aria-hidden="true" class="fa fa-eye">
                        </i>
                    </a>';
            }
            $str .= '<a class="btn btn-success btn-sm" href="'.url('campaign', [$c->campaignID, 'copy']).'" title="'.trans('messages.buttons.common_copy').'">
                    <i class="fa fa-copy">
                    </i>
                </a>
                <a class="btn btn-danger btn-sm" href="javascript:void(0)" onclick="deleteCampaign(\''.$c->title.'\',\''.$c->campaignID.'\')" title="'.trans('messages.buttons.common_delete').'">
                    <i class="fa fa-close">
                    </i>
                </a>';
            if ($c->sendDate !== null) {
                $str .= '<a class="btn btn-warning btn-sm" href="javascript:void(0)" onclick="archiveCampaign(\''.$c->title.'\',\''.$c->campaignID.'\')" title="'.trans('messages.buttons.archive').'">
                        <i aria-hidden="true" class="fa fa-archive">
                        </i>
                    </a>';
            }

            return (string) $str;
        });
        // ->paginate(10);
        $collection->rawColumns(['thumb', 'status', 'action']);

        return $collection->make(true);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        //->with('emails', 'emails.urls', 'email_count', 'emails.url_count')

        $campaigns = Campaign::where('orgID', $this->currentPerson->defaultOrgID)
            ->with('emails.click_count')
            ->with('mailgun')
            ->withCount('emails', 'urls')
            ->orderBy('campaignID', 'DESC')
            ->whereNull('archived_date')
            ->paginate(10);

        return view('v1.auth_pages.campaigns.campaigns', compact('campaigns'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->currentPerson = Person::find(auth()->id());
        $org = Org::find($this->currentPerson->defaultOrgID);
        $campaign_name = 'Untitled Campaign '.date('Y-m-d H:i:s', time());
        $list_dp = $this->generateEmailList();

        return view('v1.auth_pages.campaigns.add-edit_campaign', compact('org', 'campaign_name', 'list_dp'));
    }

    private function storeCampaignEmailTemplate($request, $new_campaign = false)
    {
        $campaign = new Campaign;
        $this->currentPerson = Person::find(auth()->id());
        $campaign->orgID = $this->currentPerson->defaultOrgID;
        $campaign_name = 'Untitled Campaign '.date('Y-m-d H:i:s', time());
        $campaign->title = $request->input('name');
        if ($new_campaign) {
            $campaign->title = $campaign_name;
        }
        $campaign->fromName = $request->input('from_name');
        $campaign->fromEmail = $request->input('from_email');
        $campaign->replyEmail = $request->input('from_email');
        $campaign->subject = $request->input('subject');
        $campaign->preheader = $request->input('preheader');
        $campaign->emailListID = $request->input('email_list');
        $campaign->creatorID = $this->currentPerson->personID;
        $campaign->updaterID = $this->currentPerson->personID;
        $campaign->content = $request->input('content');
        $content = $request->input('contentArr');
        if (empty(request()->input('subject'))) {
            $campaign->subject = $campaign->title;
        }
        $campaign->save();
        $raw_html = '';
        foreach ($content as $key => $value) {
            if (isset($value['id'])) {
                EmailCampaignTemplateBlock::create([
                    'campaign_id' => $campaign->campaignID,
                    'block_id' => $value['id'],
                    'content' => $value['content'],
                ]);
                $raw_html .= $value['content'];
            } else {
                EmailCampaignTemplateBlock::create([
                    'campaign_id' => $campaign->campaignID,
                    'block_id' => 0,
                    'content' => $value['content'],
                ]);
                $raw_html .= $value['content'];
            }
        }
        generateEmailTemplateThumbnail($html = $campaign->content, $campaign = $campaign, $currentPerson = $this->currentPerson);

        return $campaign;
    }

    private function updateCampaignEmailTemplate($campaign, $request)
    {
        $campaign->title = $request->input('name');
        $campaign->fromName = $request->input('from_name');
        $campaign->fromEmail = $request->input('from_email');
        $campaign->replyEmail = $request->input('from_email');
        $campaign->subject = $request->input('subject');
        $campaign->preheader = $request->input('preheader');
        $campaign->emailListID = $request->input('email_list');
        $campaign->content = $request->input('content');
        $content = $request->input('contentArr');
        if (empty($campaign->subject)) {
            $campaign->subject = $campaign->title;
        }
        $campaign->updaterID = $this->currentPerson->personID;
        $campaign->save();
        $content = $request->input('contentArr');
        $raw_html = '';
        if (! empty($content) && count($content) > 0) {
            EmailCampaignTemplateBlock::where('campaign_id', $campaign->campaignID)->delete();
            foreach ($content as $key => $value) {
                if (isset($value['id'])) {
                    $raw_html .= $value['content'];
                    EmailCampaignTemplateBlock::create([
                        'campaign_id' => $campaign->campaignID,
                        'block_id' => $value['id'],
                        'content' => $value['content'],
                    ]);
                }
            }
        }
        generateEmailTemplateThumbnail($html = $raw_html, $campaign = $campaign, $currentPerson = $this->currentPerson);
    }

    /**
     * create campiagn and store template
     */
    public function storeEmailTemplate(Request $request): JsonResponse
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
     */
    public function updateEmailTemplate(Request $request): JsonResponse
    {
        $campaign_id = $request->input('id');
        $campaign = Campaign::find($campaign_id);
        $current_datetime = Carbon::now()->toDateTimeString();
        if (empty($campaign)) {
            return response()->json(['success' => false, 'errors' => ['Campaign not found!']]);
        }
        $content = $request->input('contentArr');
        if (empty($content)) {
            return response()->json(['success' => false, 'errors' => ['Template Empty please some elements']]);
        }
        if (! empty($campaign->sendDate)) {
            if (! empty($campaign->scheduleDate) && $campaign->scheduleDate > $current_datetime) {
                EmailQueue::where('campaign_id', $campaign->campaignID)->delete();
                $campaign->scheduleDate = null;
                $campaign->sendDate = null;
                $campaign->save();
                $this->updateCampaignEmailTemplate($campaign, $request);
            } else {
                $campaign = $this->storeCampaignEmailTemplate($request, true);
                request()->session()->flash('alert-success', trans('messages.messages.campaign_created_from_existing'));

                return response()->json(['success' => true, 'message' => 'Template Updated', 'redirect' => url('campaign', $campaign->campaignID, 'edit')]);
            }
        } else {
            $this->updateCampaignEmailTemplate($campaign, $request);

            return response()->json(['success' => true, 'message' => 'Template Updated']);
        }
    }

    /**
     * get email template list for popup with pagination
     *
     * @return json with pagination html
     */
    public function getEmailTemplates(Request $request): JsonResponse
    {
        $campaigns = Campaign::where('orgID', $this->currentPerson->defaultOrgID)
            ->orWhere('orgID', 1)
            ->orderBy('orgID', 'ASC')
            ->orderBy('campaignID', 'desc')->paginate(12);
        $pages = $campaigns->links();
        foreach ($campaigns as $key => $value) {
            $campaigns[$key]->thumbnail = getEmailTemplateThumbnailURL($value);
        }

        return response()->json(['success' => true, 'list' => $campaigns, 'pages' => $pages->toHtml()]);
    }

    /**
     * store email template html for preview
     */
    public function storeEmailTemplateForPreview(Request $request): JsonResponse
    {
        $html = $request->input('html');
        $campaign = $request->input('campaign');
        if (! empty($campaign)) {
            $campaign = Campaign::where('campaignID', $campaign)->get()->first();
        }
        $html = replaceUserDataInEmailTemplate($email = null, $campaign = $campaign, $for_preview = true, $raw_html = $html);
        $file_name = Str::random(40).'.html';
        $tmp_path = Storage::disk('local')->put($file_name,
            view('v1.auth_pages.campaigns.preview_email_template')
                ->with(['html' => $html])->render());
        // Build pack is needed in heroku to run this
        // also below argunment are not safe but are requied to run this library on heroku
        //https://github.com/jontewks/puppeteer-heroku-buildpack and node
        $img = Browsershot::html($html)
            ->fullPage()
            ->fit(Manipulations::FIT_CONTAIN, 400, 400)
            ->addChromiumArguments(['no-sandbox', 'disable-setuid-sandbox'])
            ->save(Storage::disk('local')->path($file_name.'.png'));

        return response()->json(['success' => true, 'preview_url' => url('preview-email-template', $file_name)]);
    }

    /**
     * preview saved template
     */
    public function previewEmailTemplate(Request $request, string $filename): json
    {
        if (Storage::disk('local')->exists($filename)) {
            return Storage::disk('local')->get($filename);
        } else {
            abort(404);
        }
    }

    /**
     * show thumbnail image
     */
    public function getemailTemplateThumbnailImage(Request $request, string $filename): BinaryFileResponse
    {
        if (Storage::disk('local')->exists($filename)) {
            $path = Storage::disk('local')->path($filename);
            $headers = ['Content-Type' => 'image/png'];

            return response()->file($path, $headers);
        } else {
            abort(404);
        }
    }

    /**
     * get only blocks of email template used after loading  popup
     *
     * @param  Request  $request  [description]
     * @return [type]           [description]
     */
    public function getEmailTemplateBlocks(Request $request): JsonResponse
    {
        $campaign = Campaign::find($request->input('id'));
        if (empty($campaign)) {
            return response()->json(['success' => false, 'message' => 'Template not found!']);
        }

        return response()->json(['success' => true, 'blocks' => $campaign->template_blocks]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //dd(request()->all());
        $this->currentPerson = Person::find(auth()->id());
        $test_emails = [];

        // 1. store the campaign object
        $c = new Campaign;
        $c->orgID = $this->currentPerson->defaultOrgID;
        $c->title = 'Campaign';
        $c->fromName = request()->input('from_name');
        $c->fromEmail = request()->input('from_email');
        $c->replyEmail = request()->input('from_email');
        $c->subject = request()->input('subject');
        $c->preheader = request()->input('preheader');
        $c->content = request()->input('content');
        $c->creatorID = $this->currentPerson->personID;
        $c->updaterID = $this->currentPerson->personID;
        $c->save();

        // 2. Prep for any test emails and send if 'Send Test Message"
        $note = request()->input('note');
        for ($i = 1; $i <= 5; $i++) {
            $e = request()->input('email'.$i);
            if (! empty($e)) {
                request()->session()->flash('alert-info', 'Test message(s) sent.');
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

        return redirect(env('APP_URL').'/campaigns');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        $campaign = Campaign::findOrFail($id);
        $this->currentPerson = Person::find(auth()->id());
        $org = Org::find($this->currentPerson->defaultOrgID);
        $list_dp = $this->generateEmailList();
        $current_datetime = Carbon::now()->toDateTimeString();

        return view('v1.auth_pages.campaigns.add-edit_campaign', compact('org', 'campaign', 'list_dp', 'current_datetime'));
    }

    public function show_campaign(Campaign $campaign): View
    {
        $content = $campaign->content;

        return view('v1.auth_pages.campaigns.generic_campaign_email', compact('content'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     */
    public function edit(Campaign $campaign): View
    {
        $campaign->load('mailgun');
        $campaign->load('campaign_links');
        $this->currentPerson = Person::find(auth()->id());
        $org = Org::find($this->currentPerson->defaultOrgID);
        $list_dp = $this->generateEmailList();
        $current_datetime = Carbon::now()->toDateTimeString();

        return view('v1.auth_pages.campaigns.add-edit_campaign', compact('org', 'campaign', 'list_dp', 'current_datetime'));

        // $this->currentPerson = Person::find(auth()->id());
        // $org                 = Org::find($this->currentPerson->defaultOrgID);
        // return view('v1.auth_pages.campaigns.email_builder', compact('campaign', 'org'));
    }

    private function generateEmailList()
    {
        $list_dp = [];
        $list_dp[''] = 'Select Mailing List';
        $defaults = getDefaultEmailList($this->currentPerson, $for_select = true);
        $lists = getEmailList($this->currentPerson, $for_select = true);
        foreach ($defaults as $key => $value) {
            $list_dp[$value['id']] = $value['name'].'('.$value['count'].')';
        }
        foreach ($lists as $key => $value) {
            $list_dp[$value['id']] = $value['name'].'('.$value['count'].')';
        }

        return $list_dp;
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        //
    }

    /**
     * test method to check variable parsing
     *
     * @param  Request  $request  [description]
     * @return [type]           [description]
     */
    public function sendTestEmail(Request $request): JsonResponse
    {
        $currentPerson = $this->currentPerson;
        $email_1 = $request->input('email1');
        $email_2 = $request->input('email2');
        $email_3 = $request->input('email3');
        $email_4 = $request->input('email4');
        $email_5 = $request->input('email5');
        $valid_email = [];
        if (! empty($email_1) && filter_var($email_1, FILTER_VALIDATE_EMAIL)) {
            $valid_email[] = $email_1;
        }
        if (! empty($email_2) && filter_var($email_2, FILTER_VALIDATE_EMAIL)) {
            $valid_email[] = $email_2;
        }
        if (! empty($email_3) && filter_var($email_3, FILTER_VALIDATE_EMAIL)) {
            $valid_email[] = $email_3;
        }
        if (! empty($email_4) && filter_var($email_4, FILTER_VALIDATE_EMAIL)) {
            $valid_email[] = $email_4;
        }
        if (! empty($email_5) && filter_var($email_5, FILTER_VALIDATE_EMAIL)) {
            $valid_email[] = $email_5;
        }
        if (count($valid_email) == 0) {
            return response()->json(['success' => false, 'message' => trans('messages.errors.no_valid_email')]);
        }
        $html = $request->input('html');
        if (empty($html)) {
            return response()->json(['success' => false, 'message' => trans('messages.errors.empty_template')]);
        }
        $note = $request->input('note');
        $campaign = $request->input('campaign');
        $subject = 'Test Email';
        $from_email = $currentPerson->login;
        $sender = $currentPerson->login;
        if (! empty($campaign)) {
            $campaign = Campaign::where('campaignID', $campaign)->get()->first();
            if (! empty($campaign)) {
                $subject = 'Test : '.$campaign->subject;
                if (! empty($campaign->fromEmail)) {
                    $from_email = $campaign->fromEmail;
                }
            }
        }
        $html = replaceUserDataInEmailTemplate(null, null, true, $html);
        try {
            $mail = Mail::send('v1.auth_pages.campaigns.email_template_with_note', ['html' => $html, 'note' => $note],
                function ($message) use ($valid_email, $subject, $from_email) {
                    $message->from($from_email);
                    $message->sender($from_email);
                    $message->to($valid_email);
                    $message->subject($subject);
                }
            );

            return response()->json(['success' => true, 'message' => trans('messages.messages.test_email_sent')]);
        } catch (\Throwable $ex) {
            return response()->json(['success' => false, 'message' => trans('messages.messages.test_email_sent_failed')]);
        }
    }

    public function copy(Request $request, $campaign_id): RedirectResponse
    {
        $campaign = Campaign::findOrFail($campaign_id);
        $campaign_name_count = Campaign::where('orgID', $this->currentPerson->defaultOrgID)
            ->where('title', 'LIKE', "$campaign->title%")
            ->get()->count();
        $new_campaign = $campaign->replicate();
        if ($campaign_name_count == 0) {
            $new_campaign->title = $campaign->title.'-Copy';
        } else {
            if (strpos($campaign->title, '-Copy')) {
                $new_name = substr($campaign->title, 0, strpos($campaign->title, '-Copy'));
            }
        }
        if (strlen($new_campaign->title) > 55) {
            $new_campaign->title = $campaign->title;
        }
        $date_time = Carbon::now()->toDateTimeString();
        $new_campaign->title = 'Untitled Campaign '.$date_time;
        $new_campaign->sendDate = null;
        $new_campaign->scheduleDate = null;
        $new_campaign->save();
        $email_block = EmailCampaignTemplateBlock::where('campaign_id', $campaign->campaignID)->get();
        $raw_html = '';
        foreach ($email_block as $key => $value) {
            $new_block = $value->replicate();
            $new_block->campaign_id = $new_campaign->campaignID;
            $new_block->save();
            $raw_html .= $new_block->content;
        }
        generateEmailTemplateThumbnail($html = $raw_html, $campaign = $new_campaign, $currentPerson = $this->currentPerson);
        request()->session()->flash('alert-success', trans('messages.messages.campaign_copied_successfully'));

        return redirect(url('campaign', $new_campaign->campaignID, 'edit'));
    }

    public function sendCampaign(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'schedule' => 'nullable|date',
            'campaign' => 'required|exists:org-campaign,campaignID',
            'from_name' => 'required|max:255|min:3',
            'from_email' => 'required|email',
            'subject' => 'required|max:255|min:3',
            'preheader' => 'nullable|max:255|min:3',
            'email_list' => 'required|exists:email-list,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'validation_error' => $validator->errors()]);
        }
        $schedule = $request->input('schedule');
        $campaign_id = $request->input('campaign');
        $campaign = Campaign::where('campaignID', $campaign_id)->get()->first();
        $campaign->fromEmail = $request->input('from_email');
        $campaign->subject = $request->input('subject');
        $campaign->preheader = $request->input('preheader');
        $campaign->emailListID = $request->input('email_list');
        if (! empty($campaign->scheduleDate) && empty($schedule)) {
            $campaign->scheduleDate = null;
        }
        $campaign->save();
        $date_schedule = '';
        if (! empty($schedule)) {
            $date_time = explode(' ', $schedule, 2);
            $date = date('Y-m-d', strtotime($date_time[0]));
            $time = date('H:i', strtotime($date_time[1]));
            $dt = $date.' '.$time;
            $date = Carbon::createFromFormat('Y-m-d H:i', $dt);
            $date_schedule = $date->format('Y-m-d H:i:s');
            $campaign->scheduleDate = $date_schedule;
            $campaign->save();
        }
        $list_id = $campaign->emailListID;
        $org_id = $this->currentPerson->defaultOrgID;
        $contacts = getEmailListContact($list_id, $org_id);
        $insert_queue = [];
        EmailQueue::where('campaign_id', $campaign_id)->delete();
        $slt[] = 'mufaddal@systango.com'; /// for testing only
        $slt[] = 'testmik2149@gmail.com'; /// for testing only
        $slt[] = 'mufaddal@systango.com'; /// for testing only
        $slt[] = 'mufaddal@systango.com'; /// for testing only
        foreach ($contacts as $key => $value) {
            // $value     = 'mufaddal@systango.com'; // for testing only
            // $value     = $slt[$key];// for testing only
            $to_insert = ['campaign_id' => $campaign_id, 'org_id' => $org_id, 'email_id' => $value];
            if (! empty($schedule)) {
                $to_insert['scheduled_datetime'] = $date_schedule;
            }
            $insert_queue[] = $to_insert;
        }
        $var = EmailQueue::insert($insert_queue);
        $links = getAllLinksFromCampaignHTML($campaign);
        if (! empty($links)) {
            $url = [];
            foreach ($links as $key => $value) {
                $url[] = [
                    'campaign_id' => $campaign_id,
                    'url' => $value,
                ];
            }
            EmailCampaignLink::insert($url);
        }
        if ($var) {
            $campaign->sendDate = Carbon::now(); // remove for testing only
            $campaign->save();
            dispatch(new SendCampaignEmail);
            sendGetToWakeUpDyno();
            request()->session()->flash('alert-success', trans('messages.messages.campaign_send'));

            return response()->json(['success' => true, 'message' => trans('messages.messages.campaign_send'), 'redirect' => url('campaign', $campaign->campaignID)]);
        }
    }

    public function deleteCampaign(Request $request): JsonResponse
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
            deleteCampaignThumb($campaign);
            $campaign->delete();

            return response()->json(['success' => true, 'message' => trans('messages.messages.campaign_deleted')]);
        } elseif (! empty($campaign->sendDate) && (empty($campaign->scheduleDate) || ! empty($campaign->scheduleDate))) {
            EmailCampaignTemplateBlock::where('campaign_id', $campaign->campaignID)->delete();
            EmailQueue::where('campaign_id', $campaign->campaignID)->delete();
            deleteCampaignThumb($campaign);
            $campaign->delete();

            return response()->json(['success' => true, 'message' => trans('messages.messages.campaign_deleted')]);
        }
    }

    public function mailgunWebhook(Request $request): JsonResponse
    {
        //https://mcentric-test.herokuapp.com/email_webhook
        $response = $request->all();
        $event = $response['event-data']['event'];
        try {
            $message_id = $response['event-data']['message']['headers']['message-id'];
        } catch (\Exception $e) {
            // Do nothing but skip trying to use $message_id if it's not available
            $message_id = null;
        }
        // $message_id = '20200708072347.1.CBBD554EC0F0F5D2@sandbox4aafddd7d2f14bf9a04a148823ffd090.mailgun.org';
        $email_db = EmailQueue::where(['message_id' => $message_id])->get()->first();
        if (! empty($email_db)) {
            switch ($event) {
                case 'clicked':
                    if (! empty($response['event-data']['client-info']['device-type'])) {
                        $email_db->device_type = $response['event-data']['client-info']['device-type'];
                    }
                    $email_db->click = 1;
                    $email_db->delivered = 1;
                    $email_db->save();
                    if (! empty($response['event-data']['url'])) {
                        $url = $response['event-data']['url'];
                        $link = EmailCampaignLink::where([
                            'campaign_id' => $email_db->campaign_id,
                            'url' => $url,
                        ])->get()->first();
                        if (! empty($link)) {
                            $row = [
                                'email_campaign_links_id' => $link->id,
                                'email_queue_id' => $email_db->id,
                            ];
                            $queue_link = EmailQueueLink::where($row)->get()->first();
                            if (empty($queue_link)) {
                                $link->total_clicks = $link->total_clicks + 1;
                                $link->unique_clicks = $link->unique_clicks + 1;
                                $link->first_click = Carbon::now();
                                $link->first_click = $link->first_click;
                                $link->save();
                                EmailQueueLink::insert($row);
                            } else {
                                $link->total_clicks = $link->total_clicks + 1;
                                $link->last_click = Carbon::now();
                                $link->save();
                            }
                        }
                    }
                    break;
                case 'delivered':
                    $email_db->delivered = 1;
                    $email_db->save();
                    break;
                case 'opened':
                    if (! empty($response['event-data']['client-info']['device-type'])) {
                        $email_db->device_type = $response['event-data']['client-info']['device-type'];
                    }
                    $email_db->open = 1;
                    $email_db->save();
                    break;
                case 'failed':
                    if (! empty($response['event-data']['severity'])) {
                        if ($response['event-data']['severity'] == 'temporary') {
                            $email_db->temporary_failure = 1;
                            $email_db->save();
                        } else {
                            $email_db->permanent_fail = 1;
                            $email_db->save();
                        }
                    }
                    break;
                case 'complained':
                    $email_db->spam = 1;
                    $email_db->delivered = 1;
                    $email_db->save();
                    break;
                case 'unsubscribed':
                    $email_db->unsubscribe = 1;
                    $email_db->delivered = 1;
                    $email_db->save();
                    break;
            }
        }
        // use Illuminate\Support\Facades\Log; to enable log
        Log::info('User failed to login.', ['id' => $event, 'message_id' => json_encode($response)]);

        return response()->json(['success' => true, 'message' => 'Web-hook triggered']);
    }

    public function archiveCampaign(Request $request): JsonResponse
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
            return response()->json(['success' => false, 'message' => trans('messages.errors.campaign_archive_not_send')]);
        }
        if (! empty($campaign->sendDate) && ! empty($campaign->scheduleDate)) {
            if (Carbon::parse($campaign->scheduleDate)->gt(Carbon::now())) {
                return response()->json(['success' => false, 'message' => trans('messages.errors.campaign_archive_scheduled_send')]);
            }
        }
        $campaign->archived_date = Carbon::now()->toDateTimeString();
        $campaign->save();

        return response()->json(['success' => true, 'message' => trans('messages.messages.campaign_deleted')]);
    }

    public function urlClickedEmailList(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'campaign' => 'required|exists:org-campaign,campaignID',
            'url_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            $error = $validator->errors();
            $error = $error->all();

            return response()->json(['success' => false, 'message' => $error]);
        }
        $campaign_id = $request->input('campaign');
        $url_id = $request->input('url_id');
        $url_list = EmailCampaignLink::where(['campaign_id' => $campaign_id, 'id' => $url_id])->get()->first();
        if (empty($url_list)) {
            return response()->json(['success' => false, 'message' => ['url_nf' => ['Url Not Found!']]]);
        } else {
            // $email_list = EmailQueueLink::where(['email_campaign_links_id' => $url_id])->with('email_queue')->get();
            $email_list = $url_list->email_queue->toArray();

            return response()->json(['success' => true, 'email_list' => $email_list, 'url' => $url_list->url]);
        }
    }
}

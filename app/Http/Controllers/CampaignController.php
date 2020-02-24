<?php

namespace App\Http\Controllers;

use App\Campaign;
use App\Jobs\CampaignEmail;
use App\Org;
use App\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
class CampaignController extends Controller
{
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
            ->get();
//dd($campaigns);
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
        return view('v1.auth_pages.campaigns.add-edit_campaign', compact('org'));
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
                // $this->bulkEmail($c);
                $details             = new Collection();
                $details->toEmail    = $e;
                $details->fromName   = $c->fromName;
                $details->fromEmail  = $c->fromEmail;
                $details->replyEmail = $c->replyEmail;
                $details->subject    = $c->subject;
                $details->preheader  = $c->preheader;
                $details->content    = $c->content;
                // CampaignEmail::dispatch($details);
                dispatch(new \App\Jobs\CampaignEmail($details));


                // Mail::send(
                //     'v1.auth_pages.campaigns.generic_campaign_email',
                //     ['content' => $c->content],
                //     function ($message) use ($c, $e) {
                //         $message->from($c->fromEmail, $c->fromName);
                //         $message->sender($c->fromEmail, $c->fromName);
                //         $message->to($e, $name = null);
                //         $message->subject($c->subject);
                //         // Create a custom header that we can later retrieve
                //         //$message->getHeaders()->addTextHeader('X-Model-ID',$model->id);
                //     }
                // );
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
        //
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
        return view('v1.auth_pages.campaigns.add-edit_campaign', compact('campaign', 'org'));
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

    public function bulkEmail($campain)
    {
        $details = [
            'from_email' => 'recipient@example.com',
            'from_email' => 'recipient@example.com',
            'from_email' => 'recipient@example.com',
            'from_email' => 'recipient@example.com',
        ];
        CampaignEmail::dispatchNow($details);
        // CampaignEmail
        // Mail::send(
        //             'v1.auth_pages.campaigns.generic_campaign_email',
        //             ['content' => $c->content],
        //             function ($message) use ($c, $e) {
        //                 $message->from($c->fromEmail, $c->fromName);
        //                 $message->sender($c->fromEmail, $c->fromName);
        //                 $message->to($e, $name = null);
        //                 $message->subject($c->subject);
        //                 // Create a custom header that we can later retrieve
        //                 //$message->getHeaders()->addTextHeader('X-Model-ID',$model->id);
        //             }
        //         );
    }
}

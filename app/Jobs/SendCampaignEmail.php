<?php

namespace App\Jobs;

use App\Campaign;
use App\Models\EmailQueue;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;
use Mailgun\Mailgun;

class SendCampaignEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries        = 1;
    public $timeout      = 120;
    public $display_name = 'Campaign Email';
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email_queue = EmailQueue::where('sent', 0)->where(function ($q) {
            $q->orWhereDate('scheduled_datetime', '<=', Carbon::now()->toDateTimeString());
            $q->orWhereNull('scheduled_datetime');
        })->chunk(100, function ($data) {
            foreach ($data as $key => $value) {
                $campaign = Campaign::where('campaignID', $value->campaign_id)->get()->first();
                $html     = replaceUserDataInEmailTemplate($value->email_id, $campaign);
                $reply_to = $campaign->replyEmail;
                if (empty($reply_to)) {
                    $reply_to = $campaign->fromEmail;
                }
                $mg = Mailgun::create(env("MAILGUN_API_KEY")); // For US servers
                //first domain parameter requires only domain in local we have to put api too which wont work here
                // $response = $mg->messages()->send('sandboxdbb4c7116f3a4e0d9ea8a9026d387e02.mailgun.org', [
                if (!empty(env("APP_ENV")) && (env("APP_ENV") == 'local' || env("APP_ENV") == 'test')) {
                    $mail = Mail::raw($html, function ($message) use ($campaign, $value, $html) {
                        $message->from($campaign->fromEmail);
                        $message->to($value->email_id);
                        $message->subject($campaign->subject);
                        $message->setBody($html);
                    }
                    );
                    // $value->failed    = true;
                    $value->sent      = 1;
                    $value->delivered = 1;
                    $value->open      = rand(0, 1);
                    $value->click     = rand(0, 1);
                    $value->save();
                    continue;
                }
                $response = $mg->messages()->send(env("MAILGUN_DOMAIN"), [
                    'from'    => $campaign->fromEmail,
                    'to'      => $value->email_id,
                    'subject' => $campaign->subject,
                    'html'    => $html,
                ]);
                if (empty($response->getId())) {
                    $value->failed = true;
                    $value->sent   = 1;
                    $value->save();
                } else {
                    $value->message_id = str_replace(['<', '>'], '', $response->getId());
                    $value->sent       = 1;
                    $value->save();
                }

                // return $this->view('v1.auth_pages.campaigns.email_template_with_note', ['html' => $html, 'note' => $campaign->perheader])
                //     ->from($campaign->fromEmail, $campaign->fromName)
                //     ->subject($campaign->subject)
                //     ->replyTo($reply_to);
                // $var = Mail::to($value->email_id)->send(new EmailForQueuing($value));
                // $value->delete();
            }
        });
    }
}

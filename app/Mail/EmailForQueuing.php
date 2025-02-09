<?php

namespace App\Mail;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailForQueuing extends Mailable
{
    use Queueable, SerializesModels;

    protected $email_queue;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email_queue)
    {
        $this->email_queue = $email_queue;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        try {
            $campaign = Campaign::where('campaignID', $this->email_queue->campaign_id)->get()->first();
            $html = replaceUserDataInEmailTemplate($this->email_queue->email_id, $campaign);
            $reply_to = $campaign->replyEmail;
            if (empty($reply_to)) {
                $reply_to = $campaign->fromEmail;
            }

            return $this->view('v1.auth_pages.campaigns.email_template_with_note', ['html' => $html, 'note' => $campaign->preheader])
                ->from($campaign->fromEmail, $campaign->fromName)
                ->subject($campaign->subject)
                ->replyTo($reply_to);
        } catch (Exception $ex) {
            // dd($ex->getMessage());
        }
    }
}

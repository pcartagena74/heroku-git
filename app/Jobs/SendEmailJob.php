<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $from_email;
    protected $to_email;
    protected $subject;
    protected $details;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // $email = new EmailForQueuing();
        $c = (object) $this->details;
        // dd($c);?
        // Mail::to($this->details['email'])->send($email);
        Mail::send(
            'v1.auth_pages.campaigns.generic_campaign_email',
            ['content' => $c->content],
            function ($message) use ($c) {
                $message->from($c->fromEmail, $c->fromName);
                // $message->sender($c->fromEmail, $c->fromName);
                $message->to($c->toEmail, $name = null);
                $message->subject($c->subject);
                // Create a custom header that we can later retrieve
                //$message->getHeaders()->addTextHeader('X-Model-ID',$model->id);
            }
        );


        // CREATE INDEX person_firstName_IDX USING BTREE ON `mcen-bk`.person (firstName,lastName);

    }
}

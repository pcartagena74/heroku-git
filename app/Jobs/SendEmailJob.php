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

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 160;

    /**
     * user defined varaibles
     */
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
        $c = $this->details;
        $c->toEmail = 'mufaddal@systango.com';
        // dd([$c->fromEmail,$c->toEmail,$c->fromName,$c->subject]);
        // Mail::to($this->details['email'])->send($email);
        try {
            Mail::send('v1.auth_pages.campaigns.generic_campaign_email',
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
        } catch (Exception $ex) {
            dd($ex);
        }
    }
}

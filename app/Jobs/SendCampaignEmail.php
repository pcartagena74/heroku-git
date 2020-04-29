<?php

namespace App\Jobs;

use App\Mail\EmailForQueuing;
use App\Models\EmailQueue;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class SendCampaignEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries        = 3;
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
        $email_queue = EmailQueue::where('retry', 0)->where(function ($q) {
            $q->orWhereDate('scheduled_datetime', '<=', Carbon::now()->toDateTimeString());
            $q->orWhereNull('scheduled_datetime');
        })->chunk(100, function ($data) {
            foreach ($data as $key => $value) {
                $var          = Mail::to($value->email_id)->send(new EmailForQueuing($value));
                $value->delete();
            }
        });
    }
}

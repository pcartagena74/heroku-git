<?php

namespace App\Jobs;

use App\Notifications\MemeberImportExcelNotification;
use App\Person;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyUserOfCompletedImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $person;
    public $count;

    public $tries   = 2;
    // public $timeout = 160;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Person $person,$count)
    {
        $this->person = $person;
        $this->count = $count;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->person->notify(new MemeberImportExcelNotification($this->person,$this->count));
    }
}

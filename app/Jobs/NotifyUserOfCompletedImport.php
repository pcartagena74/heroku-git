<?php

namespace App\Jobs;

use App\Models\Importdetail;
use App\Models\Person;
use App\Notifications\MemeberImportExcelNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyUserOfCompletedImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $person;
    public $import_detail;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Person $person, Importdetail $import_detail)
    {
        $this->person = $person;
        $this->import_detail = $import_detail;
        $this->copy = Person::find(357);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->person->notify(new MemeberImportExcelNotification($this->person, $this->import_detail));
        // Send a copy of the report to Phil to monitor these transactions.
        $this->copy->notify(new MemeberImportExcelNotification($this->copy, $this->import_detail));
    }
}

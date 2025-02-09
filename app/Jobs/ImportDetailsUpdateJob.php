<?php

namespace App\Jobs;

use App\Models\Importdetail;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportDetailsUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $import_detail;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Importdetail $import_detail)
    {
        $this->import_detail = $import_detail;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->import_detail->refresh();
        $this->import_detail->completed_at = Carbon::now();
        $this->import_detail->save();
    }
}

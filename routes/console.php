<?php

use App\Jobs\SendCampaignEmail;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Heroku supports only 10 mins we will take care of reset from job itself
Schedule::job(new SendCampaignEmail)->everyThirtyMinutes();

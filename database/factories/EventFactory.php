<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Event;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(Event::class, function (Faker $faker, $params) {
    $m = rand(1, 5);
    $tz = '-0500';
    $future_date = Carbon::create(
        Carbon::now()->addMonth($m)->year,
        Carbon::now()->addMonth($m)->month,
        Carbon::now()->day,
        rand(0, 23), 0, 0, $tz);

    return [
        'eventName' => $faker->sentence(4),
        'eventDescription' => $faker->paragraph,
        'eventStartDate' => $future_date,
        'eventEndDate' => $future_date->addHour(1),
        'eventTimeZone' => $tz,
        'eventTypeID' => 1,
        'slug' => $faker->unique()->word,
        'locationID' => 1,
    ];
});

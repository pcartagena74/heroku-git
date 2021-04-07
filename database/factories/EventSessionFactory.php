<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\EventSession;
use Faker\Generator as Faker;

$factory->define(EventSession::class, function (Faker $faker, $params) {
    return [
        'sessionName' => $faker->word(rand(3, 5)),
        'sessionAbstract' => $faker->paragraph(3),
        'maxAttendees' => 50,
    ];
});

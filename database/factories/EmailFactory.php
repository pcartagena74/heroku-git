<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Email;
use Faker\Generator as Faker;

$factory->define(Email::class, function (Faker $faker, $params) {
    try {
        $person = $params['person'];
        $personID = $person->personID;
    } catch (Exception $e) {
        $person = null;
        $personID = null;
    }

    if ($person !== null) {
        return [
            'emailADDR' => $faker->unique()->safeEmail,
        ];
    } else {
        return [
            'personID' => $personID,
            'emailADDR' => $faker->unique()->safeEmail,
        ];
    }
});

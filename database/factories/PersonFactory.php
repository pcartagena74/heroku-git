<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Person;
use Faker\Generator as Faker;

$factory->define(Person::class, function (Faker $faker, $params) {

    return [
        'firstName' => $faker->firstName,
        'lastName' => $faker->lastName,
        'login' => $faker->unique()->safeEmail,
        'prefName' => $faker->firstName,
    ];
});

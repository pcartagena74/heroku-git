<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Location;
use Faker\Generator as Faker;

$factory->define(Location::class, function (Faker $faker, $param) {
    return [
        'locName' => $faker->cityPrefix,
        'addr1'   => $faker->streetAddress,
        'addr2'   => $faker->secondaryAddress,
        'city'    => $faker->city,
        'state'   => $faker->state,
        'zip'     => $faker->postcode,
    ];
});

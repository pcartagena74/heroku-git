<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\OrgPerson;
use Faker\Generator as Faker;

$factory->define(OrgPerson::class, function (Faker $faker, $params) {

    // OrgStat1 should be passed as a boolean so that OrgStat1 (and any RelDate fields) get set

    return [
        'OrgStat1' => $faker->unique()->randomNumber(rand(5, 7)),
    ];
});

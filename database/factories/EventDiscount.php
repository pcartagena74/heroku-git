<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\EventDiscount;
use Faker\Generator as Faker;

$factory->define(EventDiscount::class, function (Faker $faker, $params) {
    if (!empty($params['event_id']) && !empty($params['org_id']) && !empty($params['discount_code']) && !empty($params['percent'])) {
        return [
            'orgID'        => $params['org_id'],
            'eventID'      => $params['event_id'],
            'discountCODE' => $params['discount_code'],
            'percent'      => $params['percent'],
        ];
    } else {
        return [];
    }

});

<?php

return [

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'avatars' => [
            'driver' => 'local',
            'root' => storage_path('app/public/avatars'),
        ],

        's3_receipts' => [
            'driver' => 's3',
            'key' => env('AWS_KEY'),
            'secret' => env('AWS_SECRET'),
            'region' => env('AWS_REGION'),
            'bucket' => env('AWS_BUCKET2'),
            'visibility' => 'public',
        ],

        's3_media' => [
            'driver' => 's3',
            'key' => env('AWS_KEY'),
            'secret' => env('AWS_SECRET'),
            'region' => env('AWS_REGION'),
            'bucket' => env('AWS_BUCKET3'),
            'visibility' => 'public',
        ],

        's3_temp' => [
            'driver' => 's3',
            'key' => env('AWS_KEY'),
            'secret' => env('AWS_SECRET'),
            'region' => env('AWS_REGION'),
            'bucket' => env('AWS_BUCKET4'),
            'visibility' => 'public',
        ],

        'events' => [
            'driver' => 's3',
            'key' => env('AWS_KEY'),
            'secret' => env('AWS_SECRET'),
            'region' => env('AWS_REGION'),
            'bucket' => env('AWS_BUCKET1'),
            'visibility' => 'public',
        ],
    ],

];

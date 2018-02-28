<?php

return [

    'pdf' => [
        'enabled' => true,
        'binary'  => env('PDF_LOC'),
        'timeout' => 300,
        'options' => ['load-error-handling' => 'ignore'],
        'env'     => [],
    ],
    'image' => [
        'enabled' => true,
        'binary'  => env('IMG_LOC'),
        'timeout' => 300,
        'options' => [],
        'env'     => [],
    ],

];

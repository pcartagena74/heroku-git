<?php

return array(

    'pdf' => array(
        'enabled' => true,
        'binary'  => env('PDF_LOC'),
        'timeout' => false,
        'options' => array(),
        'env'     => array(),
    ),
    'image' => array(
        'enabled' => true,
        'binary'  => env('IMG_LOC'),
        'timeout' => false,
        'options' => array(),
        'env'     => array(),
    ),

);

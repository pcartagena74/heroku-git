<?php

return array(

    'pdf' => array(
        'enabled' => true,
        'binary'  => env('PDF_LOC'),
        'timeout' => 300,
        'options' => array('load-error-handling' => 'ignore'),
        'env'     => array(),
    ),
    'image' => array(
        'enabled' => true,
        'binary'  => env('IMG_LOC'),
        'timeout' => 300,
        'options' => array(),
        'env'     => array(),
    ),

);

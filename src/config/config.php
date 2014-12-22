<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ElasticSeach configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure the settings for the elastic search server
    |
    */

    'host'   => 'http://localhost',
    'port'   => '9200',
    'prefix' => '',
    'auth'   => false,

    /*
    |--------------------------------------------------------------------------
    | HTTP basic authentication parameters
    |--------------------------------------------------------------------------
    |
    | HTTP authentication
    | 
    | Options: [Basic, Digests, NTLM, Any]
    |
    */
    'auth' => [
        'username' => '',
        'password' => '',
        'option'   => 'Basic',
    ]

];

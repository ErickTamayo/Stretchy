<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ElasticSeach configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure the settings for the elastic search server.
    |
    */

    'hosts'  => 'http://localhost',
    'port'   => '9200',
    'prefix' => 'test',

    /*
    |--------------------------------------------------------------------------
    | HTTP basic authentication parameters
    |--------------------------------------------------------------------------
    |
    | HTTP authentication. Leave it blank if no auth is required.
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

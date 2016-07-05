<?php
return [
    'settings' => [
        'displayErrorDetails' => true,
        'production' => false, // to use dev or production settings

        // Security settings
        'security' => [
            'usedevices' => true, // manage different devices on the same service
            'ekey' => 'd8943a8c6f946266872d9553b8fd81b1', // specify random 32 bytes key [you can use bin2hex(openssl_random_pseudo_bytes(16))]
            'akey' => '81a4f692048de6fbd4297171a526f5c5', // specify random 32 bytes key  
        ],       

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
        ],

        // ReadBean dev settings
        'redbean.dev' => [
            'freeze' => false,
            'host' => 'localhost',
            'dbname' => 'linkame',
            'user' => 'root',
            'password' => '',
        ],
        // ReadBean production settings
        'redbean.prod' => [
            'freeze' => true,
            'host' => '',
            'dbname' => '',
            'user' => '',
            'password' => '',
        ],
    ],
];

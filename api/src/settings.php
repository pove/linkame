<?php
return [
    'settings' => [
        'displayErrorDetails' => true,
        'production' => false, // to use dev or production settings

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

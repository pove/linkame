<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::DEBUG));
    return $logger;
};

// redbean
$container['redbean'] = function ($c) {
    // discrimine settings production/development
    $production = $c->get('settings')['production'];
    if ($production === true)
        $settings = $c->get('settings')['redbean.prod'];
    else
        $settings = $c->get('settings')['redbean.dev'];
        
    $readbean = new RedBeanPHP\R();
    $readbean->setup('mysql:host='.$settings['host'].';dbname='.$settings['dbname'],
        $settings['user'], $settings['password']);
        
    $readbean->freeze($settings['freeze']);
    
    return $readbean;
};

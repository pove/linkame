<?php
// Application middleware

// Retrieving IP address
$app->add(new RKA\Middleware\IpAddress());
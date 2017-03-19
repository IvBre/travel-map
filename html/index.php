<?php

require_once __DIR__ . '/../vendor/autoload.php';

use TravelMap\TravelMapApplication;

$app = new TravelMapApplication([ 'debug' => true ]);


$app->get('/', 'TravelMap\\Controller\\Main::index');
$app->get('/login', 'TravelMap\\Controller\\Main::login');
$app->get('/logout', 'TravelMap\\Controller\\Main::logout');

$app->run();
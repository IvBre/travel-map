<?php

require_once __DIR__ . '/../vendor/autoload.php';

use TravelMap\TravelMapApplication;

$app = new TravelMapApplication();
$app['debug'] = true;

$app->get('/', 'TravelMap\\Controller\\Main::index');

$app->match('/logout', function () {
})->bind('logout');

$app->run();
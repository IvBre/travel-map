<?php

require_once __DIR__ . '/../vendor/autoload.php';

use TravelMap\TravelMapApplication;

$app = new TravelMapApplication();
$app['debug'] = true;

$app->get('/', 'TravelMap\\Controller\\Main::index')
    ->bind('home');
$app->get('/events', 'TravelMap\\Controller\\Main::events')
    ->bind('events');
$app->get('/events-count', 'TravelMap\\Controller\\Main::eventsCount')
    ->bind('events-count');
$app->get('/import', 'TravelMap\\Controller\\Main::import')
    ->bind('import');
$app->get('/shareToken', 'TravelMap\\Controller\\Main::shareToken')
    ->bind('get-share-token');
$app->get('/share/{token}', 'TravelMap\\Controller\\Main::share')
    ->bind('share');

$app->match('/logout', function () {
})->bind('logout');

$app->run();
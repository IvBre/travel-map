<?php
date_default_timezone_set('Europe/Berlin');

require_once __DIR__ . '/../vendor/autoload.php';

use TravelMap\TravelMapApplication;

$app = new TravelMapApplication();
$app['debug'] = true;

$app->run();
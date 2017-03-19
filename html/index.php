<?php

require_once __DIR__ . '/../vendor/autoload.php';

use TravelMap\TravelMapApplication;

$app = new TravelMapApplication();
$app['debug'] = true;
$app['google_client_secret'] = '../app/google_oauth2_client_secret.json';

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'host' => 'mysql',
        'dbname' => 'travel-map',
        'user' => 'root',
        'password' => 'secret',
    ),
));

$app->register(new Silex\Provider\SessionServiceProvider());

$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => [
        'login' => array(
            'pattern' => '^/login$',
        ),
        'secured_area' => [
            'pattern' => '^.*$',
            'form' => array('login_path' => '/login', 'check_path' => '/login_check'),
            'logout' => array(
                'logout_path' => '/logout',
            ),
        ]
    ]
));

$app->get('/', 'TravelMap\\Controller\\Main::index');
$app->get('/login', 'TravelMap\\Controller\\Main::login');
$app->get('/logout', 'TravelMap\\Controller\\Main::logout');

$app->run();
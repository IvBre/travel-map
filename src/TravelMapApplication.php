<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 5:34 PM
 */

namespace TravelMap;

use Silex\Application;
use TravelMap\Provider\AppProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\SessionServiceProvider;

final class TravelMapApplication extends Application {

    public function __construct(array $values = []) {
        $values = $this->loadConfig($values);

        parent::__construct($values);

        $this->register(new DoctrineServiceProvider(), [
            'db.options' => include_once $this['app_config_path'] . "/db.php",
        ]);

        $this->register(new SessionServiceProvider());

        $this->register(new SecurityServiceProvider(), [
            'security.firewalls' => [
                'login' => [
                    'pattern' => '^/login$',
                ],
                'secured_area' => [
                    'pattern' => '^.*$',
                    'form' => [ 'login_path' => '/login', 'check_path' => '/login_check' ],
                    'logout' => [
                        'logout_path' => '/logout',
                    ],
                ]
            ]
        ]);

        $this->register(new AppProvider());
    }

    private function loadConfig($values = []) {
        $default = [
            'base_path' => dirname('../'),
            'app_config_path' => dirname('../app/config'),
            'base_url' => 'http://localhost',
            'google_client_secret' => '../app/config/google/google_oauth2_client_secret.json',
        ];

        return array_merge($default, $values);
    }
}
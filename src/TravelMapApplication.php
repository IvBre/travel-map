<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 5:34 PM
 */

namespace TravelMap;

use Silex\Application;
use TravelMap\Provider\AppProvider;

final class TravelMapApplication extends Application {

    public function __construct(array $values = []) {
        $values = $this->loadConfig($values);

        parent::__construct($values);

        $this->register(new AppProvider());
    }

    private function loadConfig($values = []) {
        $default = [
            'base_path' => dirname('../'),
            'app_config_path' => dirname('../app/config'),
            'google_client_secret' => '../app/config/google/google_oauth2_client_secret.json',
        ];

        return array_merge($default, $values);
    }
}
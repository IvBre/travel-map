<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 5:34 PM
 */

namespace TravelMap\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\SessionServiceProvider;

final class AppProvider implements ServiceProviderInterface {

    /** @inheritdoc */
    public function register(Container $pimple) {
        $pimple->register(new DoctrineServiceProvider(), array(
            'db.options' => include_once $pimple['app_config_path'] . "/db.php",
        ));

        $pimple->register(new SessionServiceProvider());

        $pimple->register(new SecurityServiceProvider(), array(
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
    }
}
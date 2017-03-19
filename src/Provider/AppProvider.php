<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 5:34 PM
 */

namespace TravelMap\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use TravelMap\Provider\Authentication\AuthenticationProviderFactory;
use TravelMap\Provider\Authentication\GoogleOauth2Provider;
use TravelMap\Repository\UserRepository;

final class AppProvider implements ServiceProviderInterface {

    /** @inheritdoc */
    public function register(Container $app) {

        $app['repository.user'] = function () use ($app) {
            return new UserRepository($app['db']);
        };

        $app['users'] = function () use ($app) {
            return new UserProvider($app['repository.user']);
        };

        // ----------------   Providers   ---------------- //
        $app['provider.google_oauth2'] = function () use ($app) {
            return new GoogleOauth2Provider($app['google_client_secret'], $app['base_url']);
        };

        $app['provider.factory'] = function() use($app) {
            $factory = new AuthenticationProviderFactory();
            $factory->addProvider($app['provider.google_oauth2']);

            return $factory;
        };
    }
}
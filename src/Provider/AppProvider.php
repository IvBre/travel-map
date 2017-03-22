<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 5:34 PM
 */

namespace TravelMap\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use TravelMap\Importer\GoogleImporter;
use TravelMap\Repository\OAuthTokenRepository;
use TravelMap\Repository\UserRepository;

final class AppProvider implements ServiceProviderInterface {

    /** @inheritdoc */
    public function register(Container $app) {

        $app['repository.user'] = function () use ($app) {
            return new UserRepository($app['db']);
        };

        $app['repository.oauth_token'] = function () use ($app) {
            return new OAuthTokenRepository($app['db']);
        };

        $app['users'] = function () use ($app) {
            return new UserProvider($app['repository.user'], $app['repository.oauth_token']);
        };

        // ------------ Importers -------------- //
        $app['importer.google'] = function () use ($app) {
            $user = $app['user'];
            return new GoogleImporter($app['google'], $user->getOAuth()->getCredentials());
        };
    }
}
<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 5:34 PM
 */

namespace TravelMap\Provider;

use GuzzleHttp\Client;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use TravelMap\Command\GoogleImportCommand;
use TravelMap\CoordinatesResolver\GoogleCoordinatesResolver;
use TravelMap\Factory\GoogleServiceFactory;
use TravelMap\Importer\GoogleImporter;
use TravelMap\Repository\Event\EventRepository;
use TravelMap\Repository\OAuthToken\OAuthTokenRepository;
use TravelMap\Repository\User\UserRepository;

/**
 * @codeCoverageIgnore
 */
final class AppProvider implements ServiceProviderInterface {

    /** @inheritdoc */
    public function register(Container $app) {
        $app['users'] = function () use ($app) {
            return new UserProvider($app['repository.user'], $app['repository.oauth_token']);
        };

        $app['guzzle.client'] = function () use ($app) {
            return new Client();
        };

        // ------------ Repositories -------------- //
        $app['repository.user'] = function () use ($app) {
            return new UserRepository($app['db']);
        };

        $app['repository.oauth_token'] = function () use ($app) {
            return new OAuthTokenRepository($app['db']);
        };

        $app['repository.event'] = function () use ($app) {
            return new EventRepository($app['db']);
        };

        // ------------ Coordinates resolver -------------- //
        $app['coordinates.resolver'] = function () use ($app) {
            return new GoogleCoordinatesResolver($app['google'], $app['guzzle.client']);
        };

        // ------------ Factories -------------- //
        $app['factory.google'] = function () use ($app) {
            return new GoogleServiceFactory($app['google'], $app['repository.oauth_token']);
        };

        // ------------ Importers -------------- //
        $app['importer.google'] = function () use ($app) {
            return new GoogleImporter($app['factory.google'], $app['repository.event'], $app['coordinates.resolver']);
        };

        // ------------ Commands -------------- //
        $app['importer.google.command'] = function () use ($app) {
            return new GoogleImportCommand($app['importer.google']);
        };
    }
}
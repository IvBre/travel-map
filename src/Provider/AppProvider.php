<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 5:34 PM
 */

namespace TravelMap\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

final class AppProvider implements ServiceProviderInterface {

    /** @inheritdoc */
    public function register(Container $app) {
        $app['provider.google_oauth2'] = $app->protect(function () use ($app) {
            return new GoogleOauth2Provider($app['google_client_secret'], $app['base_url']);
        });
    }
}
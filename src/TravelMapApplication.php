<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 5:34 PM
 */

namespace TravelMap;

use Gigablah\Silex\OAuth\OAuthServiceProvider;
use Silex\Application;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use TravelMap\Provider\AppProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\SessionServiceProvider;

final class TravelMapApplication extends Application {

    public function __construct(array $values = []) {
        $values = $this->loadConfig($values);

        parent::__construct($values);

        $app = $this;

        $this->register(new DoctrineServiceProvider(), [
            'db.options' => $values['db'],
        ]);

        $this->register(new FormServiceProvider());

        $this->register(new SessionServiceProvider());

        $this->register(new TwigServiceProvider(), [
            'twig.path' => __DIR__.'/views',
        ]);

        $this->register(new OAuthServiceProvider(), array(
            'oauth.services' => array(
//                'Facebook' => array(
//                    'key' => FACEBOOK_API_KEY,
//                    'secret' => FACEBOOK_API_SECRET,
//                    'scope' => array('email'),
//                    'user_endpoint' => 'https://graph.facebook.com/me'
//                ),
                'Google' => array(
                    'key' => $values['google']['client_id'],
                    'secret' => $values['google']['client_secret'],
                    'scope' => array(
                        'https://www.googleapis.com/auth/userinfo.email',
                        //'https://www.googleapis.com/auth/userinfo.profile',
                        'https://www.googleapis.com/auth/calendar.readonly'
                    ),
                    'user_endpoint' => 'https://www.googleapis.com/oauth2/v1/userinfo'
                ),
            )
        ));

        $this->register(new AppProvider());

        $this->register(new SecurityServiceProvider(), [
            'security.firewalls' => array(
                'default' => array(
                    'pattern' => '^/',
                    'anonymous' => true,
                    'oauth' => array(
                        'failure_path' => '/',
                        'with_csrf' => true
                    ),
                    'logout' => array(
                        'logout_path' => '/logout',
                        'with_csrf' => true
                    ),
                    'users' => $app['users']
                )
            ),
            'security.access_rules' => array(
                array('^/auth', 'ROLE_USER')
            )
        ]);

        $this->before(function (Request $request) use ($app) {
            if (isset($app['security.token_storage'])) {
                $token = $app['security.token_storage']->getToken();
            } else {
                $token = $app['security']->getToken();
            }

            $app['user'] = null;

            if ($token && !$app['security.trust_resolver']->isAnonymous($token)) {
                $app['user'] = $token->getUser();
            }
        });
    }

    private function loadConfig($values = []) {
        $default = [
            'base_path' => dirname(__DIR__) . '/',
            'app_config_path' => dirname(__DIR__) . '/app/config/',
            'base_url' => 'http://localhost',
            'google_client_secret' => '../app/config/google/google_oauth2_client_secret.json',
        ];

        $default = array_merge($default, require_once($default['app_config_path'] . "/parameters.php"));

        return array_merge($default, $values);
    }
}
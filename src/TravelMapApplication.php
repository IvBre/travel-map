<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 5:34 PM
 */

namespace TravelMap;

use Gigablah\Silex\OAuth\OAuthServiceProvider;
use Knp\Provider\ConsoleServiceProvider;
use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use TravelMap\Provider\AppProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\SessionServiceProvider;

/**
 * @codeCoverageIgnore
 */
final class TravelMapApplication extends Application {

    public function __construct(array $values = []) {
        $values = $this->loadConfig($values);

        parent::__construct($values);

        $app = $this;

        $this->register(new DoctrineServiceProvider(), [
            'db.options' => $values['db'],
        ]);

        $this->register(new SessionServiceProvider());

        $this->register(new TwigServiceProvider(), [
            'twig.path' => __DIR__ . '/Views',
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
                        'https://www.googleapis.com/auth/calendar.readonly',
                    ),
                    'additional_params' => [
                        'access_type' => 'offline'
                    ],
                    'user_endpoint' => 'https://www.googleapis.com/oauth2/v1/userinfo?access_type=offline'
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
                ['^/auth', 'ROLE_USER'],
                ['^/import', 'ROLE_USER'],
                ['^/shareToken', 'ROLE_USER']
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

        $app->register(new AssetServiceProvider(), array(
            'assets.version' => 'v1',
            'assets.version_format' => '%s?version=%s',
        ));

        $app->register(new ConsoleServiceProvider(), array(
            'console.name'              => 'Travel Map',
            'console.version'           => '1.0.0',
            'console.project_directory' => __DIR__.'/..'
        ));

        $this->loadRoutes();
    }

    private function loadConfig($values = []) {
        $default = [
            'base_path' => dirname(__DIR__) . '/',
            'app_config_path' => dirname(__DIR__) . '/app/config/',
            'base_url' => 'http://localhost',
        ];

        $parameters = json_decode(file_get_contents($default['app_config_path'] . "/parameters.json"), true);
        if ($parameters === null) {
            throw new BadCredentialsException("parameters.json file is missing from the config folder.");
        }

        $default = array_merge($default, $parameters);

        return array_merge($default, $values);
    }

    private function loadRoutes() {
        $this->get('/', 'TravelMap\\Controller\\Main::index')
            ->bind('home');
        $this->get('/events', 'TravelMap\\Controller\\Main::events')
            ->bind('events');
        $this->get('/events-count', 'TravelMap\\Controller\\Main::eventsCount')
            ->bind('events-count');
        $this->get('/import/{source}', 'TravelMap\\Controller\\Main::import')
            ->bind('import');
        $this->get('/shareToken', 'TravelMap\\Controller\\Main::shareToken')
            ->bind('get-share-token');
        $this->get('/share/{token}', 'TravelMap\\Controller\\Main::share')
            ->bind('share');

        $this->match('/logout', function () {})
            ->bind('logout');
    }
}
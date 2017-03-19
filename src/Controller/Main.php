<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 5:34 PM
 */

namespace TravelMap\Controller;

use Google_Service_Calendar;
use Silex\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use TravelMap\Entity\User;
use TravelMap\Provider\ProviderInterface;
use TravelMap\UserProvider;

final class Main {

    public function index(Application $app) {
        /** @var ProviderInterface $loginProvider */
        $loginProvider = $app['provider.google_oauth2'];
        $loginProvider->connect($app['user']);

        $events = $loginProvider->getUserEvents();

        return new Response('test: ' . $events);
    }

    public function login(Request $request, Application $app) {
        /** @var ProviderInterface $loginProvider */
        $loginProvider = $app['provider.google_oauth2'];

        if ($request->query->has('code')) {
            $accessToken = $loginProvider->getAccessToken($request->query->get('code'));

            $me = $loginProvider->getUserInfo();
            $email = $me->getEmail();

            $userProvider = new UserProvider($app['db']);
            $user = $userProvider->loadUserByUsername($email);

            if ($user === null) {
                $user = new User(null, $email, $accessToken['access_token'], $me->getFirstName(), $me->getLastName());
                $userProvider->createUser($user);
            }
            else {
                $userProvider->updateUser($user);
            }

            $token = new UsernamePasswordToken($user, null, "secured_area");
            $app["security.token_storage"]->setToken($token); //now the user is logged in

            //now dispatch the login event
            $event = new InteractiveLoginEvent($request, $token);
            $app["dispatcher"]->dispatch("security.interactive_login", $event);
        }
        else {
            $authUrl = $loginProvider->getAuthUrl();
            return new RedirectResponse($authUrl);
        }

        return new RedirectResponse('/');
    }

    public function logout() {

    }
}
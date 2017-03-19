<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 5:34 PM
 */

namespace TravelMap\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use TravelMap\Entity\User;
use TravelMap\Provider\Authentication\AuthenticationProviderFactory;
use TravelMap\Repository\UserRepository;
use TravelMap\ValueObject\AccessToken;

final class Main {

    public function index(Application $app) {
        /** @var AuthenticationProviderFactory $providerFactory */
        $providerFactory = $app['provider.factory'];
        $loginProvider = $providerFactory->getProviderByIdentifier('google');

        $events = $loginProvider->getUserEvents();

        return new Response('test: ' . $events);
    }

    public function login(Request $request, Application $app) {
        return $app['twig']->render('login.html.twig', [
            'providers' => [
                'google' => 'Log in with Google',
            ]
        ]);
    }

    public function authenticate(Request $request, Application $app) {
        /** @var AuthenticationProviderFactory $providerFactory */
        $providerFactory = $app['provider.factory'];
        $loginProvider = $providerFactory->getProviderByIdentifier($request->query->get('p'));

        if ($request->query->has('code')) {
            $accessToken = $loginProvider->getAccessToken($request->query->get('code'));

            $me = $loginProvider->getUserInfo();

            /** @var UserRepository $userRepository */
            $userRepository = $app['repository.user'];
            $user = $userRepository->getUserByEmail($me->getEmail());

            if ($user === null) {
                $user = new User(
                    null,
                    new AccessToken($accessToken['access_token']),
                    $me->getEmail(),
                    $me->getFirstName(),
                    $me->getLastName()
                );
                $userRepository->createUser($user);
            }
            else {
                $userRepository->updateUser($user);
            }

            //$app['security.token_storage']->setToken(new UsernamePasswordToken($user, 'dummy', 'secured_area', ['ROLE_USER']));

            $token = new UsernamePasswordToken($user, 'dummy', 'secured_area', ['ROLE_USER']);
            $app["security.token_storage"]->setToken($token); //now the user is logged in

            //now dispatch the login event
            $event = new InteractiveLoginEvent($request, $token);
            $app["dispatcher"]->dispatch("security.interactive_login", $event);
        }
        else {
            $authUrl = $loginProvider->getAuthUrl();
            return new RedirectResponse($authUrl);
        }
        return new Response('test');
        //return new RedirectResponse('/');
    }

    public function logout() {

    }
}
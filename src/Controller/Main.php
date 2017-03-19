<?php

namespace TravelMap\Controller;

use Google_Service_Calendar;
use Silex\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use TravelMap\Entity\User;
use TravelMap\GoogleOauth2Provider;
use TravelMap\UserProvider;

class Main {

    public function index(Request $request, Application $app) {
        $loginProvider = new GoogleOauth2Provider($app['google_client_secret']);
        $loginProvider->setClient($request);

        $service = new Google_Service_Calendar($loginProvider->getClient());

        // Print the next 10 events on the user's calendar.
        $calendarId = 'primary';
        $optParams = array(
            'maxResults' => 10,
            'orderBy' => 'startTime',
            'singleEvents' => TRUE,
            'timeMin' => date('c'),
        );
        $results = $service->events->listEvents($calendarId, $optParams);

        if (count($results->getItems()) == 0) {
            print "No upcoming events found.\n";
        } else {
            print "Upcoming events:\n";
            foreach ($results->getItems() as $event) {
                $start = $event->start->dateTime;
                if (empty($start)) {
                    $start = $event->start->date;
                }
                printf("%s (%s)\n", $event->getSummary(), $start);
            }
        }
        return new Response('test');
    }

    public function login(Request $request, Application $app) {
        $loginProvider = new GoogleOauth2Provider($app['google_client_secret']);
        $loginProvider->setClient($request);
        $userProvider = new UserProvider($app['db']);
        $user = $userProvider->getUserByEmail('slatkishar@gmail.com');

        if ($request->query->has('code')) {
            $accessToken = $loginProvider->getAccessToken($request->query->get('code'));

            $me = $loginProvider->getUserInfo();
            $email = $me->getEmail();

            $userProvider = new UserProvider($app['db']);
            $user = $userProvider->getUserByEmail($email);

            if ($user === null) {
                $user = new User(null, $email, $accessToken['access_token'], $me->getGivenName(), $me->getFamilyName());
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
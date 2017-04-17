<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 5:34 PM
 */

namespace TravelMap\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use TravelMap\Importer\ImporterInterface;

final class Main {

    public function index(Request $request, Application $app) {
        return $app['twig']->render('index.html.twig', [
            'login_paths' => $app['oauth.login_paths'],
            'logout_path' => $app['url_generator']->generate('logout', [
                '_csrf_token' => $app['oauth.csrf_token']('logout')
            ]),
            'error' => $app['security.last_error']($request),
            'api_key' => $app['google']['api_key'],
        ]);
    }

    public function events(Application $app) {
        $events = $app['repository.event']->getAllEventsByUser($app['user']->getId());

        return new JsonResponse($events);
    }

    public function eventsCount(Application $app) {
        $totalEvents = $app['repository.event']->getCountOfAllEventsByUser($app['user']->getId());

        return new JsonResponse([ 'total' => $totalEvents ]);
    }

    public function import(Application $app) {
        /** @var ImporterInterface $importer */
        $importer = $app['importer.google'];

        $importer->execute();

        $app['session']->getFlashBag()->add('info', "Events are being imported in the background. 
            The page will automatically refresh when new events are imported.");

        return new RedirectResponse('/');
    }

    public function share($token, Application $app) {
        $email = base64_decode($token);

        return $app['twig']->render('share.html.twig');
    }
}
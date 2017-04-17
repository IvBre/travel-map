<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 5:34 PM
 */

namespace TravelMap\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use TravelMap\Entity\User;
use TravelMap\Exception\NotFoundException;
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

    public function events(Request $request, Application $app) {
        $userId = $this->getUserId($request, $app);

        $events = $app['repository.event']->getAllEventsByUser($userId);

        return new JsonResponse($events);
    }

    public function eventsCount(Request $request, Application $app) {
        $userId = $this->getUserId($request, $app);
        $totalEvents = $app['repository.event']->getCountOfAllEventsByUser($userId);

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

    public function shareToken(Application $app) {
        $shareToken = $app['repository.user']->getShareTokenByUserId($app['user']->getId());

        return new JsonResponse([ 'shareToken' => $shareToken ]);
    }

    public function share($token, Application $app) {
        $user = $app['repository.user']->getUserByShareToken($token);

        if ($user === null) {
            $app['session']->getFlashBag()->add('error', 'The requested map does not exists.');
            return new RedirectResponse('/');
        }

        return $app['twig']->render('share.html.twig', [
            'user' => $user,
            'api_key' => $app['google']['api_key'],
            'token' => $token,
        ]);
    }

    private function getUserId(Request $request, Application $app) {
        if ($request->query->has('st')) {
            $sharedToken = $request->query->get('st');
            /** @var User $user */
            $user = $app['repository.user']->getUserByShareToken($sharedToken);
            if ($user === null) {
                throw new NotFoundException("The requested user does not exists.");
            }
            return $user->getId();
        }
        elseif (isset($app['user'])) {
            return $app['user']->getId();
        }

        throw new AccessDeniedException("You are not allowed to access these pages.");
    }
}
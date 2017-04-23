<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 5:34 PM
 */

namespace TravelMap\Controller;

use Psr\Log\InvalidArgumentException;
use Silex\Application;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Process\Process;
use TravelMap\Entity\User;

/**
 * @codeCoverageIgnore
 */
final class Main {

    /**
     * @param Request $request
     * @param Application $app
     */
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

    /**
     * @param Request $request
     * @param Application $app
     * @return JsonResponse
     */
    public function events(Request $request, Application $app) {
        $userId = $this->getUserId($request, $app);

        $events = $app['repository.event']->getAllEventsByUser($userId);

        return new JsonResponse($events);
    }

    /**
     * @param Request $request
     * @param Application $app
     * @return JsonResponse
     */
    public function eventsCount(Request $request, Application $app) {
        $userId = $this->getUserId($request, $app);
        $totalEvents = $app['repository.event']->getCountOfAllEventsByUser($userId);

        return new JsonResponse([ 'total' => $totalEvents ]);
    }

    /**
     * @param string $source
     * @param Application $app
     * @return RedirectResponse
     */
    public function import($source, Application $app) {
        $source = strtolower($source);
        if (!isset($app["importer.{$source}"])) {
            throw new InvalidArgumentException('Requested import source does not exist.');
        }
        $command = "{$app['base_path']}app/console import:{$source} {$app['user']->getId()} > /tmp/output.log 2>/tmp/output.log &";

        $process = new Process($command);
        $process->start();

        $app['session']->getFlashBag()->add('info', "Events are being imported in the background. 
            The page will automatically refresh when new events are imported.");

        sleep(1);
        
        return new RedirectResponse('/');
    }

    /**
     * @param Application $app
     * @return JsonResponse
     */
    public function shareToken(Application $app) {
        $shareToken = $app['repository.user']->getShareTokenByUserId($app['user']->getId());

        return new JsonResponse([ 'shareToken' => $shareToken ]);
    }

    /**
     * @param string $token
     * @param Application $app
     * @return RedirectResponse
     */
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

    /**
     * @param Request $request
     * @param Application $app
     * @return int
     * @throws AccessDeniedException
     */
    private function getUserId(Request $request, Application $app) {
        if ($request->query->has('st')) {
            $sharedToken = $request->query->get('st');
            /** @var User $user */
            $user = $app['repository.user']->getUserByShareToken($sharedToken);
            if ($user === null) {
                throw new AccessDeniedException("The requested user does not exists.");
            }
            return $user->getId();
        }
        elseif (isset($app['user']) && $app['user'] instanceof User) {
            return $app['user']->getId();
        }

        throw new AccessDeniedException("You are not allowed to access these pages.");
    }
}
<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 5:34 PM
 */

namespace TravelMap\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

final class Main {

    public function index(Request $request, Application $app) {

        return $app['twig']->render('index.html.twig', array(
            'login_paths' => $app['oauth.login_paths'],
            'logout_path' => $app['url_generator']->generate('logout', array(
                '_csrf_token' => $app['oauth.csrf_token']('logout')
            )),
            'error' => $app['security.last_error']($request)
        ));
    }
}
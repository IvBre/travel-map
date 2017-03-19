<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 5:56 PM
 */

namespace TravelMap\Provider\Authentication;

use TravelMap\Entity\ExternalProfile;

/**
 * In case of a new source for the map
 * implement this interface and register it in the AppProvider
 */
interface ProviderInterface {

    public function getIdentifier();

    public function getAuthUrl();

    public function getAccessToken($code);

    /** @return ExternalProfile */
    public function getUserInfo();

    public function getUserEvents();
}
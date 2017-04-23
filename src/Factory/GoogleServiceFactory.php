<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 4/19/17
 * Time: 10:00 PM
 */

namespace TravelMap\Factory;

use Google_Client;
use Google_Service_Calendar;
use TravelMap\Repository\OAuthToken\OAuthTokenRepositoryInterface;

class GoogleServiceFactory {

    /** @var string|array */
    private $authConfig;

    /** @var OAuthTokenRepositoryInterface */
    private $oAuthTokenRepository;

    public function __construct($authConfig, OAuthTokenRepositoryInterface $oAuthTokenRepository) {
        $this->authConfig = $authConfig;
        $this->oAuthTokenRepository = $oAuthTokenRepository;
    }

    /**
     * @param $userId
     * @return Google_Service_Calendar
     */
    public function create($userId) {
        $oAuthToken = $this->oAuthTokenRepository->getLastUsedOAuthToken($userId);

        $client = new Google_Client();
        $client->setScopes([ Google_Service_Calendar::CALENDAR_READONLY ]);
        $client->setAuthConfig($this->authConfig);
        $client->setAccessToken($oAuthToken->getCredentials());

        return new Google_Service_Calendar($client);
    }
}
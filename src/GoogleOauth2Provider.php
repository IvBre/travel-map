<?php
/**
 * Created by PhpStorm.
 * User: ivana
 * Date: 1/25/17
 * Time: 9:43 PM
 */

namespace TravelMap;

use Symfony\Component\HttpFoundation\Request;

class GoogleOauth2Provider {

    /** @var \Google_Client */
    private $client;

    /**
     * @param string $clientSecret - Path to the secret client config
     */
    public function __construct($clientSecret) {
        $this->client = new \Google_Client();
        $this->client->setAuthConfig($clientSecret);
        $this->client->setScopes([
            \Google_Service_Oauth2::USERINFO_EMAIL,
            \Google_Service_Calendar::CALENDAR_READONLY
        ]);
    }

    /**
     * @param string $code
     * @return array
     */
    public function getAccessToken($code) {
        $accessToken = $this->client->fetchAccessTokenWithAuthCode($code);

        $this->client->setAccessToken($accessToken);

        // Refresh the token if it's expired.
        if ($this->client->isAccessTokenExpired()) {
            $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
            $accessToken = $this->client->getAccessToken();
        }

        return $accessToken;
    }

    /** @return string */
    public function getAuthUrl() {
        return $this->client->createAuthUrl();
    }

    /** @return \Google_Client */
    public function getClient() {
        return $this->client;
    }

    /** @return \Google_Service_Oauth2_Userinfoplus */
    public function getUserInfo() {
        $plus = new \Google_Service_Oauth2($this->client);
        return $plus->userinfo->get("me");
    }

    /** @param Request $request */
    public function setClient($request) {
        $redirect_uri = 'http://' . $request->server->get('HTTP_HOST') . $request->server->get('PHP_SELF') . '/login';
        $this->client->setRedirectUri($redirect_uri);
        $this->client->setAccessType("offline");
        $this->client->setIncludeGrantedScopes(true);
    }
}
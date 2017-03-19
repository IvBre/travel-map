<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 5:34 PM
 */

namespace TravelMap\Provider\Authentication;

use Google_Service_Calendar;
use TravelMap\Entity\ExternalProfile;
use TravelMap\ValueObject\Email;
use TravelMap\ValueObject\Name;

final class GoogleOauth2Provider implements ProviderInterface {

    /** @var \Google_Client */
    private $client;

    /**
     * @param string $clientSecret - Path to the secret client config
     * @param string $baseUrl - URI of the website
     */
    public function __construct($clientSecret, $baseUrl) {
        $this->client = new \Google_Client();
        $this->client->setAuthConfig($clientSecret);
        $this->client->setScopes([
            \Google_Service_Oauth2::USERINFO_EMAIL,
            \Google_Service_Calendar::CALENDAR_READONLY
        ]);
        $this->client->setRedirectUri($baseUrl . '/authenticate?p=' . $this->getIdentifier());
        $this->client->setAccessType("offline");
        $this->client->setIncludeGrantedScopes(true);
    }

    public function getIdentifier() {
        return 'google';
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

    /** @inheritdoc */
    public function getUserInfo() {
        $plus = new \Google_Service_Oauth2($this->client);
        $plusProfile =  $plus->userinfo->get();

        $externalProfile = new ExternalProfile(
            new Email($plusProfile->getEmail()),
            new Name($plusProfile->getGivenName()),
            new Name($plusProfile->getFamilyName())
        );

        return $externalProfile;
    }

    public function getUserEvents() {
        $service = new Google_Service_Calendar($this->client);

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
    }
}
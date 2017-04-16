<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/21/17
 * Time: 8:51 PM
 */

namespace TravelMap\Importer;

use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_EventAttendee;
use TravelMap\Entity\Event;
use TravelMap\Entity\User;
use TravelMap\Repository\EventRepository;
use TravelMap\ValueObject\Coordinates;
use TravelMap\ValueObject\DateTime;
use TravelMap\ValueObject\Name;
use TravelMap\ValueObject\Text;
use TravelMap\ValueObject\Url;

final class GoogleImporter implements ImporterInterface {

    private $authConfig;

    private $user;

    private $eventRepository;

    public function __construct($authConfig, User $user, EventRepository $eventRepository) {
        $this->authConfig = $authConfig;
        $this->user = $user;
        $this->eventRepository = $eventRepository;
    }

    public function execute() {
        $client = new Google_Client();
        $client->setScopes([ Google_Service_Calendar::CALENDAR_READONLY ]);
        $client->setAuthConfig($this->authConfig);
        $client->setAccessType('offline');
        $client->setAccessToken($this->user->getOAuth()->getCredentials());

//        if ($client->isAccessTokenExpired()) {
//            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
//            file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
//        }

        $service = new Google_Service_Calendar($client);

        // Print the next 10 events on the user's calendar.
        $calendarId = 'primary';
        $optParams = array(
            //'maxResults' => 10,
            //'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMax' => date('c'),
        );
        $results = $service->events->listEvents($calendarId, $optParams);

        if (count($results->getItems()) == 0) {
            return null;
        }
        $events = [];
        /** @var \Google_Service_Calendar_Event $event */
        foreach ($results->getItems() as $event) {
            $location = new Name($event->getLocation());
            //geo location
            $coordinates = $this->getCoordinates($location);
            // event location is not a valid location so skip this event
            if (!$coordinates) {
                continue;
            }
            $startDate = new DateTime($event->getStart()->getDateTime());
            $endDate = new DateTime($event->getEnd()->getDateTime());
            $link = new Url($event->getHtmlLink());
            $summary = new Text($event->getSummary());
            $attendees = '';
            /** @var Google_Service_Calendar_EventAttendee $attendee */
            foreach ($event->getAttendees() as $attendee) {
                $attendees.=$attendee->getAdditionalGuests();
            }
            $attendees = new Text($attendees);

            $storedEvent = $this->eventRepository->getEventByCoordinates($this->user->getId(), $coordinates);
            if ($storedEvent === null) {
                $storedEvent = $this->eventRepository->createEvent(
                    $location,
                    $coordinates,
                    $startDate,
                    $endDate,
                    $link,
                    $summary,
                    $attendees,
                    $this->user->getId()
                );
            }

            $events[] = $storedEvent;
        }

        return $events;
    }

    private function getCoordinates($address){
        $address = urlencode($address);
        $url = "http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false";
        $ch = curl_init();
        $options = array(
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => false,
        );

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);
        if (!$response) {
            return false;
        }
        $response = json_decode($response);
        if ($response->status !== 'OK') {
            return false;
        }
        $lat  = $response->results[0]->geometry->location->lat;
        $long = $response->results[0]->geometry->location->lng;

        return new Coordinates($lat, $long);
    }
}
<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/21/17
 * Time: 8:51 PM
 */

namespace TravelMap\Importer;

use Google_Client;
use Google_Service_Calendar;
use TravelMap\Entity\Event;
use TravelMap\Repository\EventRepository;
use TravelMap\ValueObject\Coordinates;
use TravelMap\ValueObject\DateTime;
use TravelMap\ValueObject\Name;
use TravelMap\ValueObject\Text;
use TravelMap\ValueObject\Url;

final class GoogleImporter implements ImporterInterface {

    private $authConfig;

    private $credentials;

    private $eventRepository;

    public function __construct($authConfig, $credentials, EventRepository $eventRepository) {
        $this->authConfig = $authConfig;
        $this->credentials = $credentials;
        $this->eventRepository = $eventRepository;
    }

    public function execute() {
        $client = new Google_Client();
        $client->setScopes([ Google_Service_Calendar::CALENDAR_READONLY ]);
        $client->setAuthConfig($this->authConfig);
        $client->setAccessType('offline');
        $client->setAccessToken($this->credentials);

//        if ($client->isAccessTokenExpired()) {
//            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
//            file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
//        }

        $service = new Google_Service_Calendar($client);

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
            return null;
        }
        $events = [];
        foreach ($results->getItems() as $event) {
            $location = new Name($event->getLocation());
            //geo location
            $coordinates = new Coordinates(123.456567, 13423.678678);
            $startDate = new DateTime($event->getStart());
            $endDate = new DateTime($event->getEnd());
            $link = new Url($event->getHtmlLink());
            $summary = new Text($event->getSummary());
            $attendees = '';
            foreach ($event->getAttendees() as $attendee) {
                $attendees.=$attendee;
            }
            $attendees = new Text($attendees);

            $events[] = $this->eventRepository->createEvent(
                $location,
                $coordinates,
                $startDate,
                $endDate,
                $link,
                $summary,
                $attendees
            );
        }

        return $events;
    }
}
<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/21/17
 * Time: 8:51 PM
 */

namespace TravelMap\Importer;

use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_CalendarListEntry;
use Google_Service_Calendar_EventAttendee;
use Symfony\Component\Console\Output\OutputInterface;
use TravelMap\Repository\EventRepository;
use TravelMap\Repository\OAuthTokenRepository;
use TravelMap\ValueObject\Coordinates;
use TravelMap\ValueObject\DateTime;
use TravelMap\ValueObject\Name;
use TravelMap\ValueObject\Text;
use TravelMap\ValueObject\Url;


final class GoogleImporter implements ImporterInterface {

    const SOURCE = 1;

    /** @var string|array */
    private $authConfig;

    /** @var OAuthTokenRepository */
    private $oAuthTokenRepository;

    /** @var EventRepository */
    private $eventRepository;

    /** @var OutputInterface */
    private $output;

    private $userId;

    public function __construct($authConfig, OAuthTokenRepository $oAuthTokenRepository, EventRepository $eventRepository) {
        $this->authConfig = $authConfig;
        $this->oAuthTokenRepository = $oAuthTokenRepository;
        $this->eventRepository = $eventRepository;
    }

    /** @inheritdoc */
    public function execute($userId, OutputInterface $output) {
        $this->userId = $userId;
        $this->output = $output;
        $oAuthToken = $this->oAuthTokenRepository->getLastUsedOAuthToken($userId);

        $client = new Google_Client();
        $client->setScopes([ Google_Service_Calendar::CALENDAR_READONLY ]);
        $client->setAuthConfig($this->authConfig);
        $client->setAccessToken($oAuthToken->getCredentials());

        // clear previous imported events
        $this->eventRepository->deleteUserEvents($userId, self::SOURCE);

        $service = new Google_Service_Calendar($client);
        $calendarList = $service->calendarList->listCalendarList();
        $result = 0;
        /** @var Google_Service_Calendar_CalendarListEntry $calendar */
        foreach ($calendarList->getItems() as $calendar) {
            $this->output->writeln("Calendar: " . $calendar->getSummary());
            $calendarId = $calendar->getId();
            $result += $this->importEvents($service, $calendarId);
        }

        $this->output->writeln("Total events imported: {$result}");
    }

    /**
     * @param Google_Service_Calendar $service
     * @param string $calendarId
     * @param string|null $nextPageToken
     * @return int
     */
    private function importEvents($service, $calendarId = 'primary', $nextPageToken = null) {
        $optParams = [
            'showHiddenInvitations' => true,
            'singleEvents' => true,
            'timeMax' => date('c'),
            'maxResults' => 500
        ];
        if ($nextPageToken !== null) {
            $optParams['pageToken'] = $nextPageToken;
        }
        $results = $service->events->listEvents($calendarId, $optParams);

        $events = 0;
        /** @var \Google_Service_Calendar_Event $event */
        $foundEvents = $results->getItems();
        $this->output->writeln("Found " . count($foundEvents) . " events.");
        foreach ($foundEvents as $event) {
            $this->output->writeln("Name: " . $event->getSummary());
            $this->output->writeln("Location: " . $event->getLocation());
            if ($event->getLocation() === null) {
                continue;
            }
            $location = new Name($event->getLocation());
            $coordinates = $this->getCoordinates($location);
            // event location is not a valid location so skip this event
            if (!$coordinates) {
                $this->output->writeln("Coordinates not available.");
                continue;
            }
            $this->output->writeln("Latitude: " . $coordinates->getLatitude() . " Longitude: " . $coordinates->getLongitude());

            $storedEvent = $this->eventRepository->getEventByCoordinates($this->userId, $coordinates);
            if ($storedEvent !== null) {
                $this->output->writeln("Event already imported.");
                continue;
            }

            $startDate = new DateTime($event->getStart()->getDateTime());
            $endDate = new DateTime($event->getEnd()->getDateTime());
            $link = new Url($event->getHtmlLink());
            $summary = new Text($event->getSummary());
            $attendees = '';
            /** @var Google_Service_Calendar_EventAttendee $attendee */
            foreach ($event->getAttendees() as $attendee) {
                $attendees.=$attendee->getDisplayName();
            }
            $attendees = new Text($attendees);

            $this->eventRepository->createEvent(
                $location,
                $coordinates,
                $startDate,
                $endDate,
                $link,
                $summary,
                $attendees,
                $this->userId,
                self::SOURCE
            );
            $this->output->writeln("Event " . $location->getName() . " imported.");
            $events++;
        }

        if ($results->getNextPageToken() !== null) {
            $this->output->writeln("Next page...");
            return $events + $this->importEvents($service, $calendarId, $results->getNextPageToken());
        }

        return $events;
    }

    /**
     * @param string $address
     * @return bool|Coordinates
     */
    private function getCoordinates($address){
        $address = urlencode($address);
        $url = "https://maps.google.com/maps/api/geocode/json?address=$address&key={$this->authConfig['api_key']}&sensor=false";

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
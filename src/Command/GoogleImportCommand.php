<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 4/16/17
 * Time: 10:12 PM
 */

namespace TravelMap\Command;

use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_CalendarListEntry;
use Google_Service_Calendar_EventAttendee;
use Knp\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TravelMap\Repository\EventRepository;
use TravelMap\Repository\OAuthTokenRepository;
use TravelMap\ValueObject\Coordinates;
use TravelMap\ValueObject\DateTime;
use TravelMap\ValueObject\Name;
use TravelMap\ValueObject\Text;
use TravelMap\ValueObject\Url;

final class GoogleImportCommand extends Command {

    /** @var string|array */
    private $authConfig;

    /** @var OAuthTokenRepository */
    private $oAuthTokenRepository;

    /** @var EventRepository */
    private $eventRepository;

    public function __construct($authConfig, OAuthTokenRepository $oAuthTokenRepository, EventRepository $eventRepository) {
        parent::__construct();
        $this->authConfig = $authConfig;
        $this->oAuthTokenRepository = $oAuthTokenRepository;
        $this->eventRepository = $eventRepository;
    }

    /** @inheritdoc */
    protected function configure() {
        $this
            ->setName("import:google")
            ->setDescription("Execute the import of events from Google")
            ->addArgument('userId', InputArgument::REQUIRED, "User ID to import events for.");
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $userId = $input->getArgument('userId');
        $oAuthToken = $this->oAuthTokenRepository->getLastUsedOAuthToken($userId);

        $client = new Google_Client();
        $client->setScopes([ Google_Service_Calendar::CALENDAR_READONLY ]);
        $client->setAuthConfig($this->authConfig);
        $client->setAccessToken($oAuthToken->getCredentials());

        // clear previous imported events
        $this->eventRepository->deleteUserEvents($userId);

        $service = new Google_Service_Calendar($client);
        $calendarList = $service->calendarList->listCalendarList();
        $result = 0;
        /** @var Google_Service_Calendar_CalendarListEntry $calendar */
        foreach ($calendarList->getItems() as $calendar) {
            $output->writeln("Calendar: " . $calendar->getSummary());
            $calendarId = $calendar->getId();
            $result += $this->importEvents($output, $service, $userId, $calendarId);
        }

        $output->writeln("Total events imported: {$result}");

        return 0;
    }

    /**
     * @param OutputInterface $output
     * @param Google_Service_Calendar $service
     * @param int $userId
     * @param string $calendarId
     * @param string|null $nextPageToken
     * @return int
     */
    private function importEvents(OutputInterface $output, $service, $userId, $calendarId = 'primary', $nextPageToken = null) {
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
        $output->writeln("Found " . count($foundEvents) . " events.");
        foreach ($foundEvents as $event) {
            $output->writeln("Name: " . $event->getSummary());
            $output->writeln("Location: " . $event->getLocation());
            if ($event->getLocation() === null) {
                continue;
            }
            $location = new Name($event->getLocation());
            $coordinates = $this->getCoordinates($location);
            // event location is not a valid location so skip this event
            if (!$coordinates) {
                $output->writeln("Coordinates not available.");
                continue;
            }
            $output->writeln("Latitude: " . $coordinates->getLatitude() . " Longitude: " . $coordinates->getLongitude());

            $storedEvent = $this->eventRepository->getEventByCoordinates($userId, $coordinates);
            if ($storedEvent !== null) {
                $output->writeln("Event already imported.");
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
                $userId
            );
            $output->writeln("Event " . $location->getName() . " imported.");
            $events++;
        }

        if ($results->getNextPageToken() !== null) {
            $output->writeln("Next page...");
            return $events + $this->importEvents($output, $service, $userId, $calendarId, $results->getNextPageToken());
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
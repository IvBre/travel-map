<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/21/17
 * Time: 8:51 PM
 */

namespace TravelMap\Importer;

use Google_Service_Calendar;
use Google_Service_Calendar_CalendarListEntry;
use Google_Service_Calendar_EventAttendee;
use Symfony\Component\Console\Output\OutputInterface;
use TravelMap\CoordinatesResolver\CoordinatesResolverInterface;
use TravelMap\Factory\GoogleServiceFactory;
use TravelMap\Repository\Event\EventRepositoryInterface;
use TravelMap\ValueObject\DateTime;
use TravelMap\ValueObject\Name;
use TravelMap\ValueObject\Text;
use TravelMap\ValueObject\Url;


final class GoogleImporter implements ImporterInterface {

    const SOURCE = 1;

    /** @var CoordinatesResolverInterface */
    private $coordinatesResolver;

    /** @var string|array */
    private $factory;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var OutputInterface */
    private $output;

    private $userId;

    public function __construct(
        GoogleServiceFactory $factory,
        EventRepositoryInterface $eventRepository,
        CoordinatesResolverInterface $coordinatesResolver
    ) {
        $this->factory = $factory;
        $this->eventRepository = $eventRepository;
        $this->coordinatesResolver = $coordinatesResolver;
    }

    /** @inheritdoc */
    public function execute($userId, OutputInterface $output) {
        $this->userId = $userId;
        $this->output = $output;

        $service = $this->factory->create($userId);

        // clear previous imported events
        $this->eventRepository->deleteUserEvents($userId, self::SOURCE);

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
    private function importEvents($service, $calendarId = 'primary', $nextPageToken = null)
    {
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
            $coordinates = $this->coordinatesResolver->resolve($location);
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
                $attendees .= $attendee->getDisplayName();
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
}
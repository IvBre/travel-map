<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 4/19/17
 * Time: 9:08 PM
 */

namespace TravelMap\Test\Importer;

use \Google_Service_Calendar;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Output\OutputInterface;
use TravelMap\CoordinatesResolver\CoordinatesResolverInterface;
use TravelMap\Factory\GoogleServiceFactory;
use TravelMap\Importer\GoogleImporter;
use TravelMap\Repository\Event\EventRepository;
use Doctrine\DBAL\Connection;
use TravelMap\ValueObject\Coordinates;

/**
 * @coversDefaultClass \TravelMap\Importer\GoogleImporter
 */
class GoogleImporterTest extends TestCase {

    /**
     * @covers ::__construct
     * @covers ::execute
     * @covers ::importEvents
     * @param int $userId
     * @param int $calendarId
     * @param string $calendarSummary
     * @param array $expectedEvents
     * @dataProvider getUserIdDataProvider
     */
    public function testExecute($userId, $calendarId, $calendarSummary, $expectedEvents) {
        $service = $this->prophesize(Google_Service_Calendar::class);
        $calendarList = $this->prophesize(\Google_Service_Calendar_Resource_CalendarList::class);

        $calendarItem = new \Google_Service_Calendar_CalendarListEntry();
        $calendarItem->setSummary($calendarSummary);
        $calendarItem->setId($calendarId);

        $calendarCalendarList = new \Google_Service_Calendar_CalendarList();
        $calendarCalendarList->setItems([
            $calendarItem
        ]);

        $calendarList->listCalendarList()
            ->willReturn($calendarCalendarList);
        $service->calendarList = $calendarList->reveal();

        $output = $this->prophesize(OutputInterface::class);
        $coordinatesResolver = $this->prophesize(CoordinatesResolverInterface::class);

        $events = [];
        foreach ($expectedEvents as $expectedEvent) {
            $event = new \Google_Service_Calendar_Event();
            $event->setSummary($expectedEvent['summary']);
            $event->setLocation($expectedEvent['location']);
            $start = new \Google_Service_Calendar_EventDateTime();
            $start->setDateTime($expectedEvent['start']);
            $event->setStart($start);

            $end = new \Google_Service_Calendar_EventDateTime();
            $end->setDateTime($expectedEvent['end']);
            $event->setEnd($end);

            $event->setHtmlLink($expectedEvent['link']);

            $expectedAttendees = [];
            foreach ($expectedEvent['attendees'] as $expectedAttendee) {
                $attendee = new \Google_Service_Calendar_EventAttendee();
                $attendee->setDisplayName($expectedAttendee);
                $expectedAttendees[] = $attendee;
            }
            $event->setAttendees($expectedAttendees);

            $coordinatesResolver->resolve($expectedEvent['location'])
                ->willReturn(new Coordinates($expectedEvent['lat'], $expectedEvent['lng']));

            $output->writeln("Name: " . $expectedEvent['summary'])
                ->shouldBeCalled(1);
            $output->writeln("Location: " . $expectedEvent['location'])
                ->shouldBeCalled(1);
            $output->writeln("Latitude: " . $expectedEvent['lat'] . " Longitude: " . $expectedEvent['lng'])
                ->shouldBeCalled(1);
            $output->writeln("Event " . $expectedEvent['location'] . " imported.")
                ->shouldBeCalled(1);

            $events[] = $event;
        }

        $eventResults = new \Google_Service_Calendar_Events();
        $eventResults->setItems($events);
        $eventList = $this->prophesize(\Google_Service_Calendar_Resource_Events::class);
        $eventList->listEvents($calendarId, Argument::any())
            ->willReturn($eventResults);
        $service->events = $eventList->reveal();

        $factory = $this->prophesize(GoogleServiceFactory::class);
        $factory->create($userId)
            ->willReturn($service->reveal());

        $db = $this->prophesize(Connection::class);
        $eventRepository = new EventRepository($db->reveal());

        $importer = new GoogleImporter($factory->reveal(), $eventRepository, $coordinatesResolver->reveal());

        $output->writeln("Calendar: " . $calendarSummary)
            ->shouldBeCalled(1);
        $output->writeln("Found " . count($expectedEvents) . " events.")
            ->shouldBeCalled(1);

        $output->writeln("Total events imported: " . count($expectedEvents))
            ->shouldBeCalled(1);

        $importer->execute($userId, $output->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::execute
     * @covers ::importEvents
     * @param int $userId
     * @param int $calendarId
     * @param string $calendarSummary
     * @param array $expectedEvents
     * @dataProvider getUserIdDataProvider
     */
    public function testExecuteInvalidLocation($userId, $calendarId, $calendarSummary, $expectedEvents) {
        $service = $this->prophesize(Google_Service_Calendar::class);
        $calendarList = $this->prophesize(\Google_Service_Calendar_Resource_CalendarList::class);

        $calendarItem = new \Google_Service_Calendar_CalendarListEntry();
        $calendarItem->setSummary($calendarSummary);
        $calendarItem->setId($calendarId);

        $calendarCalendarList = new \Google_Service_Calendar_CalendarList();
        $calendarCalendarList->setItems([
            $calendarItem
        ]);

        $calendarList->listCalendarList()
            ->willReturn($calendarCalendarList);
        $service->calendarList = $calendarList->reveal();

        $output = $this->prophesize(OutputInterface::class);
        $coordinatesResolver = $this->prophesize(CoordinatesResolverInterface::class);

        $events = [];
        foreach ($expectedEvents as $expectedEvent) {
            $event = new \Google_Service_Calendar_Event();
            $event->setSummary($expectedEvent['summary']);
            $event->setLocation($expectedEvent['location']);
            $start = new \Google_Service_Calendar_EventDateTime();
            $start->setDateTime($expectedEvent['start']);
            $event->setStart($start);

            $end = new \Google_Service_Calendar_EventDateTime();
            $end->setDateTime($expectedEvent['end']);
            $event->setEnd($end);

            $event->setHtmlLink($expectedEvent['link']);

            $coordinatesResolver->resolve($expectedEvent['location'])
                ->willReturn(null);

            $output->writeln("Name: " . $expectedEvent['summary'])
                ->shouldBeCalled(1);
            $output->writeln("Location: " . $expectedEvent['location'])
                ->shouldBeCalled(1);
            $output->writeln("Coordinates not available.")
                ->shouldBeCalled(1);

            $events[] = $event;
        }

        $eventResults = new \Google_Service_Calendar_Events();
        $eventResults->setItems($events);
        $eventList = $this->prophesize(\Google_Service_Calendar_Resource_Events::class);
        $eventList->listEvents($calendarId, Argument::any())
            ->willReturn($eventResults);
        $service->events = $eventList->reveal();

        $factory = $this->prophesize(GoogleServiceFactory::class);
        $factory->create($userId)
            ->willReturn($service->reveal());

        $db = $this->prophesize(Connection::class);
        $eventRepository = new EventRepository($db->reveal());

        $importer = new GoogleImporter($factory->reveal(), $eventRepository, $coordinatesResolver->reveal());

        $output->writeln("Calendar: " . $calendarSummary)
            ->shouldBeCalled(1);
        $output->writeln("Found " . count($expectedEvents) . " events.")
            ->shouldBeCalled(1);

        $output->writeln("Total events imported: 0")
            ->shouldBeCalled(1);

        $importer->execute($userId, $output->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::execute
     * @covers ::importEvents
     * @param int $userId
     * @param int $calendarId
     * @param string $calendarSummary
     * @param array $expectedEvents
     * @dataProvider getUserIdDataProvider
     */
    public function testExecuteLocationNotAvailable($userId, $calendarId, $calendarSummary, $expectedEvents) {
        $service = $this->prophesize(Google_Service_Calendar::class);
        $calendarList = $this->prophesize(\Google_Service_Calendar_Resource_CalendarList::class);

        $calendarItem = new \Google_Service_Calendar_CalendarListEntry();
        $calendarItem->setSummary($calendarSummary);
        $calendarItem->setId($calendarId);

        $calendarCalendarList = new \Google_Service_Calendar_CalendarList();
        $calendarCalendarList->setItems([
            $calendarItem
        ]);

        $calendarList->listCalendarList()
            ->willReturn($calendarCalendarList);
        $service->calendarList = $calendarList->reveal();

        $output = $this->prophesize(OutputInterface::class);
        $coordinatesResolver = $this->prophesize(CoordinatesResolverInterface::class);

        $events = [];
        foreach ($expectedEvents as $expectedEvent) {
            $event = new \Google_Service_Calendar_Event();
            $event->setSummary($expectedEvent['summary']);
            $start = new \Google_Service_Calendar_EventDateTime();
            $start->setDateTime($expectedEvent['start']);
            $event->setStart($start);

            $end = new \Google_Service_Calendar_EventDateTime();
            $end->setDateTime($expectedEvent['end']);
            $event->setEnd($end);

            $event->setHtmlLink($expectedEvent['link']);

            $coordinatesResolver->resolve($expectedEvent['location'])
                ->willReturn(new Coordinates($expectedEvent['lat'], $expectedEvent['lng']));

            $output->writeln("Name: " . $expectedEvent['summary'])
                ->shouldBeCalled(1);
            $output->writeln("Location: ")
                ->shouldBeCalled(1);

            $events[] = $event;
        }

        $eventResults = new \Google_Service_Calendar_Events();
        $eventResults->setItems($events);
        $eventList = $this->prophesize(\Google_Service_Calendar_Resource_Events::class);
        $eventList->listEvents($calendarId, Argument::any())
            ->willReturn($eventResults);
        $service->events = $eventList->reveal();

        $factory = $this->prophesize(GoogleServiceFactory::class);
        $factory->create($userId)
            ->willReturn($service->reveal());

        $db = $this->prophesize(Connection::class);
        $eventRepository = new EventRepository($db->reveal());

        $importer = new GoogleImporter($factory->reveal(), $eventRepository, $coordinatesResolver->reveal());

        $output->writeln("Calendar: " . $calendarSummary)
            ->shouldBeCalled(1);
        $output->writeln("Found " . count($expectedEvents) . " events.")
            ->shouldBeCalled(1);

        $output->writeln("Total events imported: 0")
            ->shouldBeCalled(1);

        $importer->execute($userId, $output->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::execute
     * @covers ::importEvents
     * @param int $userId
     * @param int $calendarId
     * @param string $calendarSummary
     * @param array $expectedEvents
     * @dataProvider getUserIdDataProvider
     */
    public function testExecuteAlreadyImported($userId, $calendarId, $calendarSummary, $expectedEvents) {
        $service = $this->prophesize(Google_Service_Calendar::class);
        $calendarList = $this->prophesize(\Google_Service_Calendar_Resource_CalendarList::class);

        $calendarItem = new \Google_Service_Calendar_CalendarListEntry();
        $calendarItem->setSummary($calendarSummary);
        $calendarItem->setId($calendarId);

        $calendarCalendarList = new \Google_Service_Calendar_CalendarList();
        $calendarCalendarList->setItems([
            $calendarItem
        ]);

        $calendarList->listCalendarList()
            ->willReturn($calendarCalendarList);
        $service->calendarList = $calendarList->reveal();

        $db = $this->prophesize(Connection::class);
        $db->executeQuery(Argument::cetera())
            ->shouldBeCalled(1);
        $output = $this->prophesize(OutputInterface::class);
        $coordinatesResolver = $this->prophesize(CoordinatesResolverInterface::class);

        $events = [];
        foreach ($expectedEvents as $expectedEvent) {
            $event = new \Google_Service_Calendar_Event();
            $event->setSummary($expectedEvent['summary']);
            $event->setLocation($expectedEvent['location']);
            $start = new \Google_Service_Calendar_EventDateTime();
            $start->setDateTime($expectedEvent['start']);
            $event->setStart($start);

            $end = new \Google_Service_Calendar_EventDateTime();
            $end->setDateTime($expectedEvent['end']);
            $event->setEnd($end);

            $event->setHtmlLink($expectedEvent['link']);

            $coordinatesResolver->resolve($expectedEvent['location'])
                ->willReturn(new Coordinates($expectedEvent['lat'], $expectedEvent['lng']));

            $db->fetchAssoc(Argument::cetera())
                ->willReturn([
                    'id' => $expectedEvent['id'],
                    'location' => $expectedEvent['location'],
                    'visited_from' => $expectedEvent['start'],
                    'visited_until' => $expectedEvent['end'],
                    'link' => $expectedEvent['link'],
                    'summary' => $expectedEvent['summary'],
                    'attendees' => '',
                ]);

            $output->writeln("Name: " . $expectedEvent['summary'])
                ->shouldBeCalled(1);
            $output->writeln("Location: " . $expectedEvent['location'])
                ->shouldBeCalled(1);
            $output->writeln("Latitude: " . $expectedEvent['lat'] . " Longitude: " . $expectedEvent['lng'])
                ->shouldBeCalled(1);
            $output->writeln("Event already imported.")
                ->shouldBeCalled(1);

            $events[] = $event;
        }

        $eventResults = new \Google_Service_Calendar_Events();
        $eventResults->setItems($events);
        $eventList = $this->prophesize(\Google_Service_Calendar_Resource_Events::class);
        $eventList->listEvents($calendarId, Argument::any())
            ->willReturn($eventResults);
        $service->events = $eventList->reveal();

        $factory = $this->prophesize(GoogleServiceFactory::class);
        $factory->create($userId)
            ->willReturn($service->reveal());

        $eventRepository = new EventRepository($db->reveal());

        $importer = new GoogleImporter($factory->reveal(), $eventRepository, $coordinatesResolver->reveal());

        $output->writeln("Calendar: " . $calendarSummary)
            ->shouldBeCalled(1);
        $output->writeln("Found " . count($expectedEvents) . " events.")
            ->shouldBeCalled(1);

        $output->writeln("Total events imported: 0")
            ->shouldBeCalled(1);

        $importer->execute($userId, $output->reveal());
    }

    public function getUserIdDataProvider() {
        return [
            [
                12345,
                9845967,
                'Default calendar',
                [
                    [
                        'id' => 123466,
                        'summary' => 'Trip to Berlin',
                        'location' => 'Berlin, Germany',
                        'start' => date('Y-m-d H:i:s', strtotime('-2 months')),
                        'end' => date('Y-m-d H:i:s', strtotime('-1 month')),
                        'link' => 'http://www.google.com/calendar/123466',
                        'attendees' => ['John Smith'],
                        'lat' => 45.678678,
                        'lng' => -140.45777,
                    ]
                ]
            ],
            [
                876453,
                3496456,
                'Vacation calendar',
                [
                    [
                        'id' => 99687567,
                        'summary' => 'Vacation in Palma',
                        'location' => 'Palma de Mallorca, Spain',
                        'start' => date('Y-m-d H:i:s', strtotime('-4 months')),
                        'end' => date('Y-m-d H:i:s', strtotime('-3 month')),
                        'link' => 'http://www.google.com/calendar/99687567',
                        'attendees' => ['Petar Petrovic', 'John Smith'],
                        'lat' => 89.678678,
                        'lng' => -56.45777,
                    ]
                ]
            ],
        ];
    }
}
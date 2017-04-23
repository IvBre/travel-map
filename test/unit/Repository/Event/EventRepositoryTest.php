<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 4/23/17
 * Time: 4:51 PM
 */

namespace TravelMap\Test\Repository\Event;

use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\Connection;
use Prophecy\Argument;
use TravelMap\Entity\Event;
use TravelMap\Importer\GoogleImporter;
use TravelMap\Repository\Event\EventRepository;
use TravelMap\ValueObject\Coordinates;
use TravelMap\ValueObject\DateTime;
use TravelMap\ValueObject\Name;
use TravelMap\ValueObject\Text;
use TravelMap\ValueObject\Url;

/**
 * @coversDefaultClass \TravelMap\Repository\Event\EventRepository
 */
class EventRepositoryTest extends TestCase {

    /**
     * @covers ::__construct
     * @covers ::getAllEventsByUser
     * @param int $userId
     * @param array $expectedEvents
     * @dataProvider getEventsDataProvider
     */
    public function testGetAllEventsByUser($userId, $expectedEvents) {
        $db = $this->prophesize(Connection::class);
        $db->fetchAll(Argument::cetera())
            ->willreturn($expectedEvents);
        $repository = new EventRepository($db->reveal());
        /** @var Event[] $actual */
        $actual = $repository->getAllEventsByUser($userId);

        foreach ($actual as $key => $actualEvent) {
            $expectedEvent = $expectedEvents[$key];
            $this->assertSame($expectedEvent['location'], (string)$actualEvent->getLocation());
            $this->assertSame($expectedEvent['latitude'], (string)$actualEvent->getCoordinates()->getLatitude());
            $this->assertSame($expectedEvent['longitude'], (string)$actualEvent->getCoordinates()->getLongitude());
            $this->assertSame($expectedEvent['visited_from'], (string)$actualEvent->getStart());
        }
    }

    /**
     * @covers ::__construct
     * @covers ::getAllEventsByUser
     * @param int $userId
     * @dataProvider getEventsDataProvider
     */
    public function testGetAllEventsByUserEmpty($userId) {
        $db = $this->prophesize(Connection::class);
        $db->fetchAll(Argument::cetera())
            ->willreturn([]);
        $repository = new EventRepository($db->reveal());
        /** @var Event[] $actual */
        $actual = $repository->getAllEventsByUser($userId);

        $this->assertEmpty($actual);
    }

    /**
     * @covers ::__construct
     * @covers ::getEventByCoordinates
     * @param int $userId
     * @param array $expectedEvents
     * @dataProvider getEventsDataProvider
     */
    public function testGetEventByCoordinates($userId, $expectedEvents) {
        $expectedEvent = $expectedEvents[0];
        $db = $this->prophesize(Connection::class);
        $db->fetchAssoc(Argument::cetera())
            ->willReturn($expectedEvent);
        $repository = new EventRepository($db->reveal());
        /** @var Event $actual */
        $actual = $repository->getEventByCoordinates($userId, new Coordinates($expectedEvent['latitude'], $expectedEvent['longitude']));

        $this->assertSame($expectedEvent['location'], (string)$actual->getLocation());
        $this->assertSame($expectedEvent['latitude'], (string)$actual->getCoordinates()->getLatitude());
        $this->assertSame($expectedEvent['longitude'], (string)$actual->getCoordinates()->getLongitude());
        $this->assertSame($expectedEvent['visited_from'], (string)$actual->getStart());
    }

    /**
     * @covers ::__construct
     * @covers ::getEventByCoordinates
     * @param int $userId
     * @param array $expectedEvents
     * @dataProvider getEventsDataProvider
     */
    public function testGetEventByCoordinatesEmpty($userId, $expectedEvents) {
        $expectedEvent = $expectedEvents[0];
        $db = $this->prophesize(Connection::class);
        $db->fetchAssoc(Argument::cetera())
            ->willReturn(false);
        $repository = new EventRepository($db->reveal());
        /** @var Event $actual */
        $actual = $repository->getEventByCoordinates($userId, new Coordinates($expectedEvent['latitude'], $expectedEvent['longitude']));

        $this->assertNull($actual);
    }

    /**
     * @covers ::__construct
     * @covers ::getCountOfAllEventsByUser
     * @param int $userId
     * @param array $expectedEvents
     * @dataProvider getEventsDataProvider
     */
    public function testGetCountOfAllEventsByUser($userId, $expectedEvents) {
        $total = count($expectedEvents);
        $db = $this->prophesize(Connection::class);
        $db->fetchAssoc(Argument::cetera())
            ->willReturn([
                'total' => $total
            ]);
        $repository = new EventRepository($db->reveal());
        $actual = $repository->getCountOfAllEventsByUser($userId);

        $this->assertSame($total, $actual);
    }

    /**
     * @covers ::__construct
     * @covers ::createEvent
     * @param int $userId
     * @param array $expectedEvents
     * @dataProvider getEventsDataProvider
     */
    public function testCreateEvent($userId, $expectedEvents) {
        $expectedEvent = $expectedEvents[0];
        $db = $this->prophesize(Connection::class);
        $db->insert(Argument::cetera())
            ->shouldBeCalled(1);
        $db->lastInsertId()
            ->shouldBeCalled(1);
        $repository = new EventRepository($db->reveal());

        $actual = $repository->createEvent(
            new Name($expectedEvent['location']),
            new Coordinates($expectedEvent['latitude'], $expectedEvent['longitude']),
            new DateTime($expectedEvent['visited_from']),
            new DateTime($expectedEvent['visited_until']),
            new Url($expectedEvent['link']),
            new Text($expectedEvent['summary']),
            new Text($expectedEvent['attendees']),
            $userId,
            GoogleImporter::SOURCE
        );

        $this->assertSame($expectedEvent['location'], (string)$actual->getLocation());
        $this->assertSame($expectedEvent['latitude'], (string)$actual->getCoordinates()->getLatitude());
        $this->assertSame($expectedEvent['longitude'], (string)$actual->getCoordinates()->getLongitude());
        $this->assertSame($expectedEvent['visited_from'], (string)$actual->getStart());
    }

    /**
     * @covers ::__construct
     * @covers ::deleteUserEvents
     * @param int $userId
     * @dataProvider getEventsDataProvider
     */
    public function testDeleteUserEvents($userId) {
        $db = $this->prophesize(Connection::class);
        $db->executeQuery(Argument::cetera())
            ->shouldBeCalled(1);
        $repository = new EventRepository($db->reveal());
        $repository->deleteUserEvents($userId, GoogleImporter::SOURCE);
    }

    public function getEventsDataProvider() {
        return [
            [
                123456,
                [
                    [
                        'id' => 123456,
                        'location' => 'Berlin, Germany',
                        'latitude' => '45.346456000000003',
                        'longitude' => '-123.457567',
                        'visited_from' => date('Y-m-d H:i:s', strtotime('-2 month')),
                        'visited_until' => date('Y-m-d H:i:s', strtotime('-1 month')),
                        'link' => 'http://www.google.com/calendar/item/123456',
                        'summary' => 'Trip to Berlin',
                        'attendees' => 'John Smith'
                    ],
                    [
                        'id' => 65756546,
                        'location' => 'Belgrade, Serbia',
                        'latitude' => '23.346456',
                        'longitude' => '-120.457567',
                        'visited_from' => date('Y-m-d H:i:s', strtotime('-2 weeks')),
                        'visited_until' => date('Y-m-d H:i:s', strtotime('-1 week')),
                        'link' => 'http://www.google.com/calendar/item/65756546',
                        'summary' => 'Trip to Belgrade',
                        'attendees' => 'John Smith, Petar Petrovic'
                    ]
                ]
            ],
            [
                987654,
                [
                    [
                        'id' => 43546657,
                        'location' => 'Palma de Mallorka, Spain',
                        'latitude' => '28.346456',
                        'longitude' => '-89.457567',
                        'visited_from' => date('Y-m-d H:i:s', strtotime('-2 days')),
                        'visited_until' => date('Y-m-d H:i:s', strtotime('-1 day')),
                        'link' => 'http://www.google.com/calendar/item/43546657',
                        'summary' => 'Trip to Palma',
                        'attendees' => 'John Smith'
                    ],
                ]
            ]
        ];
    }
}
<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 4/19/17
 * Time: 8:13 PM
 */

namespace TravelMap\Entity;

use PHPUnit\Framework\TestCase;
use TravelMap\ValueObject\Coordinates;
use TravelMap\ValueObject\DateTime;
use TravelMap\ValueObject\Name;
use TravelMap\ValueObject\Text;
use TravelMap\ValueObject\Url;

/**
 * @coversDefaultClass \TravelMap\Entity\Event
 */
class EventTest extends TestCase {

    /**
     * @covers ::__construct
     * @covers ::getId
     * @param int $id
     * @param Name $location
     * @param Coordinates $coordinates
     * @param DateTime $start
     * @param DateTime $end
     * @param Url $url
     * @param Text $summary
     * @param Text $attendees
     * @dataProvider getEventsDataProvider
     */
    public function testGetId($id, $location, $coordinates, $start, $end, $url, $summary, $attendees) {
        $event = new Event($id, $location, $coordinates, $start, $end, $url, $summary, $attendees);
        $actual = $event->getId();

        $this->assertSame($id, $actual);
    }

    /**
     * @covers ::__construct
     * @covers ::getLocation
     * @param int $id
     * @param Name $location
     * @param Coordinates $coordinates
     * @param DateTime $start
     * @param DateTime $end
     * @param Url $url
     * @param Text $summary
     * @param Text $attendees
     * @dataProvider getEventsDataProvider
     */
    public function testGetLocation($id, $location, $coordinates, $start, $end, $url, $summary, $attendees) {
        $event = new Event($id, $location, $coordinates, $start, $end, $url, $summary, $attendees);
        $actual = $event->getLocation();

        $this->assertSame($location, $actual);
    }

    /**
     * @covers ::__construct
     * @covers ::getCoordinates
     * @param int $id
     * @param Name $location
     * @param Coordinates $coordinates
     * @param DateTime $start
     * @param DateTime $end
     * @param Url $url
     * @param Text $summary
     * @param Text $attendees
     * @dataProvider getEventsDataProvider
     */
    public function testGetCoordinates($id, $location, $coordinates, $start, $end, $url, $summary, $attendees) {
        $event = new Event($id, $location, $coordinates, $start, $end, $url, $summary, $attendees);
        $actual = $event->getCoordinates();

        $this->assertSame($coordinates, $actual);
    }

    /**
     * @covers ::__construct
     * @covers ::getStart
     * @param int $id
     * @param Name $location
     * @param Coordinates $coordinates
     * @param DateTime $start
     * @param DateTime $end
     * @param Url $url
     * @param Text $summary
     * @param Text $attendees
     * @dataProvider getEventsDataProvider
     */
    public function testGetStart($id, $location, $coordinates, $start, $end, $url, $summary, $attendees) {
        $event = new Event($id, $location, $coordinates, $start, $end, $url, $summary, $attendees);
        $actual = $event->getStart();

        $this->assertSame($start, $actual);
    }

    /**
     * @covers ::__construct
     * @covers ::getEnd
     * @param int $id
     * @param Name $location
     * @param Coordinates $coordinates
     * @param DateTime $start
     * @param DateTime $end
     * @param Url $url
     * @param Text $summary
     * @param Text $attendees
     * @dataProvider getEventsDataProvider
     */
    public function testGetEnd($id, $location, $coordinates, $start, $end, $url, $summary, $attendees) {
        $event = new Event($id, $location, $coordinates, $start, $end, $url, $summary, $attendees);
        $actual = $event->getEnd();

        $this->assertSame($end, $actual);
    }

    /**
     * @covers ::__construct
     * @covers ::getLink
     * @param int $id
     * @param Name $location
     * @param Coordinates $coordinates
     * @param DateTime $start
     * @param DateTime $end
     * @param Url $url
     * @param Text $summary
     * @param Text $attendees
     * @dataProvider getEventsDataProvider
     */
    public function testGetLink($id, $location, $coordinates, $start, $end, $url, $summary, $attendees) {
        $event = new Event($id, $location, $coordinates, $start, $end, $url, $summary, $attendees);
        $actual = $event->getLink();

        $this->assertSame($url, $actual);
    }

    /**
     * @covers ::__construct
     * @covers ::getSummary
     * @param int $id
     * @param Name $location
     * @param Coordinates $coordinates
     * @param DateTime $start
     * @param DateTime $end
     * @param Url $url
     * @param Text $summary
     * @param Text $attendees
     * @dataProvider getEventsDataProvider
     */
    public function testGetSummary($id, $location, $coordinates, $start, $end, $url, $summary, $attendees) {
        $event = new Event($id, $location, $coordinates, $start, $end, $url, $summary, $attendees);
        $actual = $event->getSummary();

        $this->assertSame($summary, $actual);
    }

    /**
     * @covers ::__construct
     * @covers ::getAttendees
     * @param int $id
     * @param Name $location
     * @param Coordinates $coordinates
     * @param DateTime $start
     * @param DateTime $end
     * @param Url $url
     * @param Text $summary
     * @param Text $attendees
     * @dataProvider getEventsDataProvider
     */
    public function testGetAttendees($id, $location, $coordinates, $start, $end, $url, $summary, $attendees) {
        $event = new Event($id, $location, $coordinates, $start, $end, $url, $summary, $attendees);
        $actual = $event->getAttendees();

        $this->assertSame($attendees, $actual);
    }

    /**
     * @covers ::__construct
     * @covers ::jsonSerialize
     * @param int $id
     * @param Name $location
     * @param Coordinates $coordinates
     * @param DateTime $start
     * @param DateTime $end
     * @param Url $url
     * @param Text $summary
     * @param Text $attendees
     * @dataProvider getEventsDataProvider
     */
    public function testJsonSerialize($id, $location, $coordinates, $start, $end, $url, $summary, $attendees) {
        $expected = [
            'id' => $id,
            'location' => (string) $location,
            'coordinates' => [
                'lat' => (float)$coordinates->getLatitude(),
                'lng' => (float)$coordinates->getLongitude()
            ],
            'start' => (string) $start,
            'end' => (string) $end,
            'link' => (string) $url,
            'summary' => (string) $summary,
            'attendees' => (string) $attendees,
        ];
        $event = new Event($id, $location, $coordinates, $start, $end, $url, $summary, $attendees);
        $actual = $event->jsonSerialize();

        $this->assertSame($expected, $actual);
    }

    public function getEventsDataProvider() {
        return [
            [
                12345,
                new Name('Paris'),
                new Coordinates(123.456789, -40.54657),
                new DateTime('2017-03-15 00:00:00'),
                new DateTime('2017-04-05 00:00:00'),
                new Url('http://dummy.link/event/12345'),
                new Text('Lorem ipsum dolor sit amet'),
                new Text(''),
            ],
            [
                76543,
                new Name('Berlin'),
                new Coordinates(-26.456789, 125.54657),
                new DateTime('2016-12-20 00:00:00'),
                new DateTime('2017-01-03 00:00:00'),
                new Url('http://dummy.link/event/76543'),
                new Text('Bacon ipsum dolor amet drumstick strip steak rump'),
                new Text('John Smith, Petar Petrovic'),
            ],
        ];
    }
}
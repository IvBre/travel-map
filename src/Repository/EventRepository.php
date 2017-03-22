<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/21/17
 * Time: 9:06 PM
 */

namespace TravelMap\Repository;

use Doctrine\DBAL\Connection;
use TravelMap\Entity\Event;
use TravelMap\ValueObject\Coordinates;
use TravelMap\ValueObject\DateTime;
use TravelMap\ValueObject\Name;
use TravelMap\ValueObject\Text;
use TravelMap\ValueObject\Url;

final class EventRepository {

    /** @var Connection */
    private $db;

    public function __construct(Connection $db) {
        $this->db = $db;
    }

    /**
     * @param Name $location
     * @param Coordinates $coordinates
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param Url $link
     * @param Text $summary
     * @param Text $attendees
     * @return Event
     */
    public function createEvent(
        Name $location,
        Coordinates $coordinates,
        DateTime $startDate,
        DateTime $endDate,
        Url $link,
        Text $summary,
        Text $attendees
    ) {
        $this->db->insert('user', [
            'source' => 1,
            'location' => $location,
            'latitude' => $coordinates->getLatitude(),
            'longitude' => $coordinates->getLongitude(),
            'visited_from' => $startDate,
            'visited_until' => $endDate,
            'link' => $link,
            'summary' => $summary,
            'attendees' => $attendees,
        ]);
        $eventId = $this->db->lastInsertId();

        return new Event(
            $eventId,
            $location,
            $coordinates,
            $startDate,
            $endDate,
            $link,
            $summary,
            $attendees
        );
    }
}
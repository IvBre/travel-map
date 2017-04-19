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
     * @param int $userId
     * @return array
     */
    public function getAllEventsByUser($userId) {
        $query = <<<SQL
SELECT id, source, location, latitude, longitude, visited_from, visited_until, link, summary, attendees
FROM event
WHERE user_id = ?
SQL;
        $result = $this->db->fetchAll($query, [ $userId ]);

        if (!$result) {
            return [];
        }

        $events = [];
        foreach ($result as $event) {
            $events[] = new Event(
                $event['id'],
                new Name($event['location']),
                new Coordinates($event['latitude'], $event['longitude']),
                new DateTime($event['visited_from']),
                new DateTime($event['visited_until']),
                new Url($event['link']),
                new Text($event['summary']),
                new Text($event['attendees'])
            );
        }

        return $events;
    }

    /**
     * @param int $userId
     * @param Coordinates $coordinates
     * @return null|Event
     */
    public function getEventByCoordinates($userId, Coordinates $coordinates) {
        $query = <<<SQL
SELECT id, source, location, visited_from, visited_until, link, summary, attendees
FROM event
WHERE latitude = ? AND longitude = ? AND user_id = ?
SQL;
        $event = $this->db->fetchAssoc($query, [ $coordinates->getLatitude(), $coordinates->getLongitude(), $userId ]);

        if (!$event) {
            return null;
        }

        return new Event(
            $event['id'],
            new Name($event['location']),
            $coordinates,
            new DateTime($event['visited_from']),
            new DateTime($event['visited_until']),
            new Url($event['link']),
            new Text($event['summary']),
            new Text($event['attendees'])
        );
    }

    /**
     * @param int $userId
     * @return int
     */
    public function getCountOfAllEventsByUser($userId) {
        $query = <<<SQL
SELECT COUNT(*) AS total
FROM event
WHERE user_id = ?
SQL;
        $result = $this->db->fetchAssoc($query, [ $userId ]);

        return $result['total'];
    }

    /**
     * @param Name $location
     * @param Coordinates $coordinates
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param Url $link
     * @param Text $summary
     * @param Text $attendees
     * @param int $userId
     * @param int $source
     * @return Event
     */
    public function createEvent(
        Name $location,
        Coordinates $coordinates,
        DateTime $startDate,
        DateTime $endDate,
        Url $link,
        Text $summary,
        Text $attendees,
        $userId,
        $source
    ) {
        $this->db->insert('event', [
            'source' => $source,
            'location' => $location,
            'latitude' => $coordinates->getLatitude(),
            'longitude' => $coordinates->getLongitude(),
            'visited_from' => $startDate,
            'visited_until' => $endDate,
            'link' => $link,
            'summary' => $summary,
            'attendees' => $attendees,
            'user_id' => $userId
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

    /**
     * @param int $userId
     * @param int $source
     */
    public function deleteUserEvents($userId, $source) {
        $query = <<<SQL
DELETE
FROM event
WHERE user_id = ?
  AND source = ?
SQL;
        $this->db->executeQuery($query, [ $userId, $source ]);
    }
}
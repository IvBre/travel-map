<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 4/23/17
 * Time: 4:12 PM
 */

namespace TravelMap\Repository\Event;

use TravelMap\Entity\Event;
use TravelMap\ValueObject\Coordinates;
use TravelMap\ValueObject\DateTime;
use TravelMap\ValueObject\Name;
use TravelMap\ValueObject\Text;
use TravelMap\ValueObject\Url;

interface EventRepositoryInterface {

    /**
     * @param int $userId
     * @return array
     */
    public function getAllEventsByUser($userId);

    /**
     * @param int $userId
     * @param Coordinates $coordinates
     * @return null|Event
     */
    public function getEventByCoordinates($userId, Coordinates $coordinates);

    /**
     * @param int $userId
     * @return int
     */
    public function getCountOfAllEventsByUser($userId);

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
    );

    /**
     * @param int $userId
     * @param int $source
     */
    public function deleteUserEvents($userId, $source);
}
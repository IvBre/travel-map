<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/21/17
 * Time: 9:01 PM
 */

namespace TravelMap\Entity;

use TravelMap\ValueObject\Coordinates;
use TravelMap\ValueObject\DateTime;
use TravelMap\ValueObject\Name;
use TravelMap\ValueObject\Text;
use TravelMap\ValueObject\Url;

final class Event {

    public function __construct(
        $id,
        Name $location,
        Coordinates $coordinates,
        DateTime $start,
        DateTime $end,
        Url $link,
        Text $summary,
        Text $attendees) {
    }
}
<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/21/17
 * Time: 9:01 PM
 */

namespace TravelMap\Entity;

use JsonSerializable;
use TravelMap\ValueObject\Coordinates;
use TravelMap\ValueObject\DateTime;
use TravelMap\ValueObject\Name;
use TravelMap\ValueObject\Text;
use TravelMap\ValueObject\Url;

final class Event implements JsonSerializable {

    /** @var int */
    private $id;

    /** @var Name */
    private $location;

    /** @var Coordinates */
    private $coordinates;

    /** @var DateTime */
    private $start;

    /** @var DateTime */
    private $end;

    /** @var Url */
    private $link;

    /** @var Text */
    private $summary;

    /** @var Text */
    private $attendees;

    public function __construct(
        $id,
        Name $location,
        Coordinates $coordinates,
        DateTime $start,
        DateTime $end,
        Url $link,
        Text $summary,
        Text $attendees) {
        $this->id = $id;
        $this->location = $location;
        $this->coordinates = $coordinates;
        $this->start = $start;
        $this->end = $end;
        $this->link = $link;
        $this->summary = $summary;
        $this->attendees = $attendees;
    }

    /** @return int */
    public function getId() {
        return $this->id;
    }

    /** @return Name */
    public function getLocation() {
        return $this->location;
    }

    /** @return Coordinates */
    public function getCoordinates() {
        return $this->coordinates;
    }

    /** @return DateTime */
    public function getStart() {
        return $this->start;
    }

    /** @return DateTime */
    public function getEnd() {
        return $this->end;
    }

    /** @return Url */
    public function getLink() {
        return $this->link;
    }

    /** @return Text */
    public function getSummary() {
        return $this->summary;
    }

    /** @return Text */
    public function getAttendees() {
        return $this->attendees;
    }

    /** @inheritdoc */
    function jsonSerialize() {
        return [
            'id' => $this->getId(),
            'location' => (string) $this->getLocation(),
            'coordinates' => [
                'lat' => (float)$this->getCoordinates()->getLatitude(),
                'lng' => (float)$this->getCoordinates()->getLongitude()
            ],
            'start' => (string) $this->getStart(),
            'end' => (string) $this->getEnd(),
            'link' => (string) $this->getLink(),
            'summary' => (string) $this->getSummary(),
            'attendees' => (string) $this->getAttendees(),
        ];
    }
}
<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 5:19 PM
 */

namespace TravelMap\ValueObject;

final class DateTime {

    /** @var \DateTime */
    private $dateTime;

    /**
     * @param string $dateTime
     */
    public function __construct($dateTime) {
        $dateTime = new \DateTime($dateTime);

        assert($dateTime instanceof \DateTime, "Could not create DateTime object. Please check the format.");

        $this->dateTime = $dateTime;
    }

    /** @return \DateTime */
    public function getDateTime() {
        return $this->dateTime;
    }

    public function __toString() {
        return $this->dateTime->format('Y-m-d H:i:s');
    }
}
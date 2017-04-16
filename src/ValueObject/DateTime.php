<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 5:19 PM
 */

namespace TravelMap\ValueObject;

use Psr\Log\InvalidArgumentException;

final class DateTime {

    /** @var \DateTime */
    private $dateTime;

    /**
     * @param string $dateTime
     */
    public function __construct($dateTime) {
        try {
            $dateTime = new \DateTime($dateTime);
        }
        catch (\Exception $e) {
            throw new InvalidArgumentException("Could not create DateTime object. Please check the format.");
        }

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
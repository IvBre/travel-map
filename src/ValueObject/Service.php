<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/20/17
 * Time: 9:20 PM
 */

namespace TravelMap\ValueObject;

final class Service {

    /** @var string */
    private $service;

    /**
     * @param string $service
     */
    public function __construct($service) {
        assert(is_string($service), "Service needs to be a string");
        $this->service = $service;
    }

    /** @return string */
    public function getService() {
        return $this->service;
    }

    public function __toString() {
        return $this->service;
    }
}
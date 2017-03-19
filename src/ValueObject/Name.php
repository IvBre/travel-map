<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 5:16 PM
 */

namespace TravelMap\ValueObject;

final class Name {

    /** @var string */
    private $name;

    /**
     * @param string $name
     */
    public function __construct($name) {
        $this->name = $name;
    }

    /** @return string */
    public function getName() {
        return $this->name;
    }

    public function __toString() {
        return $this->name;
    }
}
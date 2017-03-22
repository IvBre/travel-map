<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/21/17
 * Time: 9:22 PM
 */

namespace TravelMap\ValueObject;

final class Text {

    /** @var string */
    private $text;

    /**
     * @param string $text
     */
    public function __construct($text) {
        assert(is_string($text), "Text needs to be a string");

        $this->text = $text;
    }

    public function __toString() {
        return $this->text;
    }
}
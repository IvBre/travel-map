<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/21/17
 * Time: 9:19 PM
 */

namespace TravelMap\ValueObject;

final class Url {

    /** @var string */
    private $url;

    /**
     * @param string $url
     */
    public function __construct($url) {
        assert(filter_var($url, FILTER_VALIDATE_URL), "Invalid URL");

        $this->url = $url;
    }

    public function __toString() {
        return $this->url;
    }

    public function getUrl() {
        return $this->url;
    }
}
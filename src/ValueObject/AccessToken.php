<?php

namespace TravelMap\ValueObject;

final class AccessToken {

    /** @var string */
    private $accessToken;

    /**
     * @param string $accessToken
     */
    public function __construct($accessToken) {
        assert(is_string($accessToken), "Access token needs to be a string");
        $this->accessToken = $accessToken;
    }

    /** @return string */
    public function getAccessToken() {
        return $this->accessToken;
    }

    public function __toString() {
        return $this->accessToken;
    }
}
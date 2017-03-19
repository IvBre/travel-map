<?php

namespace TravelMap\ValueObject;

final class AccessToken {

    /** @var string */
    private $accessToken;

    /**
     * @param string $accessToken
     */
    public function __construct($accessToken) {
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
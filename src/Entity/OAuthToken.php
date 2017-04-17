<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/20/17
 * Time: 10:02 PM
 */

namespace TravelMap\Entity;

use TravelMap\ValueObject\AccessToken;
use TravelMap\ValueObject\DateTime;
use TravelMap\ValueObject\Service;

final class OAuthToken {

    /** @var integer */
    private $id;

    /** @var integer */
    private $userId;

    /**@var Service */
    private $service;

    /** @var AccessToken */
    private $accessToken;

    /** @var AccessToken */
    private $refreshToken;

    /** @var DateTime */
    private $expiresOn;

    /** @var DateTime */
    private $created;

    /** @var DateTime */
    private $lastTimeUsed;

    public function __construct(
        $id,
        $userId,
        Service $service,
        AccessToken $accessToken,
        DateTime $expiresOn,
        DateTime $created,
        DateTime $lastTimeUsed = null,
        AccessToken $refreshToken = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->service = $service;
        $this->accessToken = $accessToken;
        $this->expiresOn = $expiresOn;
        $this->created = $created;
        $this->lastTimeUsed = $lastTimeUsed;
        $this->refreshToken = $refreshToken;
    }

    /** @return int */
    public function getId() {
        return $this->id;
    }

    /** @return Service */
    public function getService() {
        return $this->service;
    }

    /** @return AccessToken */
    public function getAccessToken() {
        return $this->accessToken;
    }

    /** @return AccessToken|null */
    public function getRefreshToken() {
        return $this->refreshToken;
    }

    /** @return DateTime */
    public function getExpiresOn() {
        return $this->expiresOn;
    }

    /** @return DateTime */
    public function getCreated() {
        return $this->created;
    }

    /** @return DateTime */
    public function getLastTimeUsed() {
        return $this->lastTimeUsed;
    }

    /** @return array */
    public function getCredentials() {
        $now = new \DateTime();
        $diff = $now->diff($this->expiresOn->getDateTime());
        return [
            'access_token' => (string) $this->accessToken,
            'refresh_token' => (string) $this->refreshToken,
            'expires_in' => $diff->format('U'),
            'expires_on' => (string) $this->expiresOn,
            'issued_at' => (string) $this->created,
        ];
    }
}
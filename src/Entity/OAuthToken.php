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

    /** @var DateTime */
    private $created;

    public function __construct($id, $userId, Service $service, AccessToken $accessToken, DateTime $created) {
        $this->id = $id;
        $this->userId = $userId;
        $this->service = $service;
        $this->accessToken = $accessToken;
        $this->created = $created;
    }
}
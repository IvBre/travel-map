<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 1/25/17
 * Time: 9:58 PM
 */

namespace TravelMap\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use TravelMap\ValueObject\AccessToken;
use TravelMap\ValueObject\DateTime;
use TravelMap\ValueObject\Email;
use TravelMap\ValueObject\Name;

final class User implements UserInterface {

    private $id;

    /** @var AccessToken */
    private $accessToken;

    /** @var Email */
    private $email;

    /** @var Name */
    private $firstName;

    /** @var Name */
    private $lastName;

    /** @var DateTime */
    private $created;

    /** @var DateTime */
    private $lastLogin;

    public function __construct(
        $id = null,
        AccessToken $accessToken,
        Email $email,
        Name $firstName,
        Name $lastName,
        DateTime $created = null,
        DateTime $lastLogin = null
    ) {
        $this->id = $id;
        $this->accessToken = $accessToken;
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->created = $created;
        $this->lastLogin = $lastLogin;
    }

    public function __toString()
    {
        return $this->getEmail();
    }

    public function getId() {
        return $this->id;
    }

    public function getAccessToken() {
        return $this->accessToken;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getFirstName() {
        return $this->firstName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function getCreated() {
        return $this->created;
    }

    public function getLastLogin() {
        return $this->lastLogin;
    }

    /** @inheritdoc */
    public function getRoles() {
        return [ 'ROLE_USER' ];
    }

    /** @inheritdoc */
    public function getPassword() {
        // we do not need a password
        return '';
    }

    /** @inheritdoc */
    public function getSalt() {
        return null;
    }

    /** @inheritdoc */
    public function getUsername() {
        return $this->email;
    }

    /** @inheritdoc */
    public function eraseCredentials() {

    }
}
<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 1/25/17
 * Time: 9:58 PM
 */

namespace TravelMap\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use TravelMap\ValueObject\DateTime;
use TravelMap\ValueObject\Email;
use TravelMap\ValueObject\Name;

final class User implements UserInterface {

    /** @var int */
    private $id;

    /** @var OAuthToken */
    private $oauth;

    /** @var Email */
    private $email;

    /** @var Name */
    private $fullName;

    /** @var DateTime */
    private $created;

    /** @var DateTime */
    private $updated;

    public function __construct(
        $id,
        Email $email,
        Name $fullName,
        DateTime $created,
        DateTime $updated = null
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->fullName = $fullName;
        $this->created = $created;
        $this->updated = $updated;
    }

    public function __toString() {
        return (string) $this->getEmail();
    }

    public function getId() {
        return $this->id;
    }

    /** @return OAuthToken */
    public function getOAuth() {
        return $this->oauth;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getFullName() {
        return $this->fullName;
    }

    public function getCreated() {
        return $this->created;
    }

    public function getUpdated() {
        return $this->updated;
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
    public function eraseCredentials() {}

    /** @param OAuthToken */
    public function setOAuth(OAuthToken $oauth) {
        $this->oauth = $oauth;
    }
}
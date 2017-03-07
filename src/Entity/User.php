<?php

/**
 * Created by PhpStorm.
 * User: ivana
 * Date: 1/25/17
 * Time: 9:58 PM
 */

namespace TravelMap\Entity;

class User {

    private $id;

    private $accessToken;

    private $email;

    private $firstName;

    private $lastName;

    private $created;

    private $lastLogin;

    public function __construct($id = null, $accessToken, $email, $firstName, $lastName, $created = null, $lastLogin = null) {
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
}
<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 6:20 PM
 */

namespace TravelMap\Entity;

use TravelMap\ValueObject\Email;
use TravelMap\ValueObject\Name;

final class ExternalProfile {

    /** @var Email */
    private $email;

    /** @var Name */
    private $firstName;

    /** @var Name */
    private $lastName;

    public function __construct(Email $email, Name $firstName, Name $lastName) {
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    /** @return Email */
    public function getEmail() {
        return $this->email;
    }

    /** @return Name */
    public function getFirstName() {
        return $this->firstName;
    }

    /** @return Name */
    public function getLastName() {
        return $this->lastName;
    }
}
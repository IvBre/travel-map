<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 5:12 PM
 */

namespace TravelMap\ValueObject;

final class Email {

    /** @var string */
    private $email;

    /**
     * @param string $email
     */
    public function __construct($email) {
        assert(filter_var($email, FILTER_VALIDATE_EMAIL) !== false, "Invalid email");

        $this->email = $email;
    }

    /** @return string */
    public function getEmail() {
        return $this->email;
    }

    public function __toString() {
        return $this->email;
    }
}
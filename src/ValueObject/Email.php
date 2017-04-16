<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 5:12 PM
 */

namespace TravelMap\ValueObject;

use Psr\Log\InvalidArgumentException;

final class Email {

    /** @var string */
    private $email;

    /**
     * @param string $email
     */
    public function __construct($email) {
        $this->validateEmail($email);

        $this->email = $email;
    }

    /** @return string */
    public function getEmail() {
        return $this->email;
    }

    public function __toString() {
        return $this->email;
    }

    /**
     * @param string $email
     * @throws InvalidArgumentException
     */
    private function validateEmail($email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException("Provided email is not in the correct format.");
        }
    }
}
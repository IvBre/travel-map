<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 4/16/17
 * Time: 9:03 AM
 */

namespace TravelMap\ValueObject;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \TravelMap\ValueObject\Email
 */
class EmailTest extends TestCase {

    /**
     * @expectedException \InvalidArgumentException
     * @covers ::__construct
     * @covers ::validateEmail
     */
    public function testConstructFailure() {
        new Email('sflksjdflksdjf');
    }

    /**
     * @param string $email
     * @covers ::__construct
     * @covers ::validateEmail
     * @covers ::getEmail
     * @dataProvider getEmails
     */
    public function testGetEmail($email) {
        $object = new Email($email);
        $actual = $object->getEmail();

        $this->assertSame($email, $actual);
    }

    /**
     * @param string $email
     * @covers ::__construct
     * @covers ::validateEmail
     * @covers ::__toString
     * @dataProvider getEmails
     */
    public function testToString($email) {
        $object = new Email($email);
        $actual = (string)$object;

        $this->assertSame($email, $actual);
    }

    public function getEmails() {
        return [
            [
                'john.smith@test.com'
            ],
            [
                'petar.petrovic@test.com'
            ]
        ];
    }
}
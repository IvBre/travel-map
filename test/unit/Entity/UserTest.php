<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 4/16/17
 * Time: 8:25 AM
 */

namespace TravelMap\Entity;

use PHPUnit\Framework\TestCase;
use TravelMap\ValueObject\AccessToken;
use TravelMap\ValueObject\DateTime;
use TravelMap\ValueObject\Email;
use TravelMap\ValueObject\Name;
use TravelMap\ValueObject\Service;

/**
 * @coversDefaultClass \TravelMap\Entity\User
 */
class UserTest extends TestCase {

    /**
     * @param int $id
     * @param Email $email
     * @param Name $name
     * @param DateTime $created
     *
     * @covers ::__construct
     * @covers ::__toString
     * @dataProvider getUserAttributes
     */
    public function testToString($id, $email, $name, $created) {
        $user = new User($id, $email, $name, $created);
        $actual = (string)$user;

        $this->assertSame((string)$email, $actual);
    }

    /**
     * @param int $id
     * @param Email $email
     * @param Name $name
     * @param DateTime $created
     *
     * @covers ::__construct
     * @covers ::getId
     * @dataProvider getUserAttributes
     */
    public function testGetId($id, $email, $name, $created) {
        $user = new User($id, $email, $name, $created);
        $actual = $user->getId();

        $this->assertSame($id, $actual);
    }

    /**
     * @param int $id
     * @param Email $email
     * @param Name $name
     * @param DateTime $created
     *
     * @covers ::__construct
     * @covers ::setOAuth
     * @covers ::getOAuth
     * @dataProvider getUserAttributes
     */
    public function testGetOAuth($id, $email, $name, $created) {
        $oAuthToken = new OAuthToken(
            123,
            555,
            new Service('google'),
            new AccessToken('lk3j45k4hjk6456j5g67'),
            new DateTime(date('Y-m-d H:i:s', strtotime('+7 days'))),
            new DateTime(date('Y-m-d H:i:s')));
        $user = new User($id, $email, $name, $created);
        $user->setOAuth($oAuthToken);
        $actual = $user->getOAuth();

        $this->assertSame($oAuthToken, $actual);
    }

    /**
     * @param int $id
     * @param Email $email
     * @param Name $name
     * @param DateTime $created
     *
     * @covers ::__construct
     * @covers ::getEmail
     * @dataProvider getUserAttributes
     */
    public function testGetEmail($id, $email, $name, $created) {
        $user = new User($id, $email, $name, $created);
        $actual = $user->getEmail();

        $this->assertSame($email, $actual);
    }

    /**
     * @param int $id
     * @param Email $email
     * @param Name $name
     * @param DateTime $created
     *
     * @covers ::__construct
     * @covers ::getFullName
     * @dataProvider getUserAttributes
     */
    public function testGetFullName($id, $email, $name, $created) {
        $user = new User($id, $email, $name, $created);
        $actual = $user->getFullName();

        $this->assertSame($name, $actual);
    }

    /**
     * @param int $id
     * @param Email $email
     * @param Name $name
     * @param DateTime $created
     *
     * @covers ::__construct
     * @covers ::getCreated
     * @dataProvider getUserAttributes
     */
    public function testGetCreated($id, $email, $name, $created) {
        $user = new User($id, $email, $name, $created);
        $actual = $user->getCreated();

        $this->assertSame($created, $actual);
    }

    /**
     * @param int $id
     * @param Email $email
     * @param Name $name
     * @param DateTime $created
     *
     * @covers ::__construct
     * @covers ::getUpdated
     * @dataProvider getUserAttributes
     */
    public function testGetUpdated($id, $email, $name, $created) {
        $updated = new DateTime(date('Y-m-d H:i:s'));
        $user = new User($id, $email, $name, $created, $updated);
        $actual = $user->getUpdated();

        $this->assertSame($updated, $actual);
    }

    /**
     * @param int $id
     * @param Email $email
     * @param Name $name
     * @param DateTime $created
     *
     * @covers ::__construct
     * @covers ::getRoles
     * @dataProvider getUserAttributes
     */
    public function testGetRoles($id, $email, $name, $created) {
        $user = new User($id, $email, $name, $created);
        $actual = $user->getRoles();

        $this->assertSame([ 'ROLE_USER' ], $actual);
    }

    /**
     * @param int $id
     * @param Email $email
     * @param Name $name
     * @param DateTime $created
     *
     * @covers ::__construct
     * @covers ::getPassword
     * @dataProvider getUserAttributes
     */
    public function testGetPassword($id, $email, $name, $created) {
        $user = new User($id, $email, $name, $created);
        $actual = $user->getPassword();

        $this->assertSame('', $actual);
    }

    /**
     * @param int $id
     * @param Email $email
     * @param Name $name
     * @param DateTime $created
     *
     * @covers ::__construct
     * @covers ::getSalt
     * @dataProvider getUserAttributes
     */
    public function testGetSalt($id, $email, $name, $created) {
        $user = new User($id, $email, $name, $created);
        $actual = $user->getSalt();

        $this->assertNull($actual);
    }

    /**
     * @param int $id
     * @param Email $email
     * @param Name $name
     * @param DateTime $created
     *
     * @covers ::__construct
     * @covers ::getUsername
     * @dataProvider getUserAttributes
     */
    public function testGetUsername($id, $email, $name, $created) {
        $user = new User($id, $email, $name, $created);
        $actual = $user->getUsername();

        $this->assertSame($email, $actual);
    }

    public function getUserAttributes() {
        return [
            [
                123,
                new Email('john.smith@test.com'),
                new Name('John Smith'),
                new DateTime(date('Y-m-d H:i:s'))
            ],
            [
                345,
                new Email('petar.petrovic@test.com'),
                new Name('Petar Petrovic'),
                new DateTime(date('Y-m-d H:i:s'))
            ]
        ];
    }
}
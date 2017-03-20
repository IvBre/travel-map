<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 6:36 PM
 */

namespace TravelMap\Test\Repository;

use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\Connection;
use Prophecy\Argument;
use TravelMap\Entity\User;
use TravelMap\Repository\UserRepository;
use TravelMap\ValueObject\DateTime;
use TravelMap\ValueObject\Email;
use TravelMap\ValueObject\Name;

/**
 * @coversDefaultClass TravelMap\Repository\UserRepository::__construct
 */
final class UserRepositoryTest extends TestCase {

    /**
     * @covers ::__construct
     * @covers ::getUserByEmail
     */
    public function testGetUserByEmail() {

        $email = new Email('test@dummy.com');

        $user = new User(
            123456,
            $email,
            new Name('John Smith'),
            new DateTime(date('Y-m-d H:i:s')),
            new DateTime(date('Y-m-d H:i:s'))
        );

        $db = $this->prophesize(Connection::class);
        $db->fetchAssoc(Argument::any(), [ $email ])
            ->willReturn([
                'id' => $user->getId(),
                'email' => (string) $user->getEmail(),
                'full_name' => (string) $user->getFullName(),
                'created' => (string) $user->getCreated(),
                'updated' => (string) $user->getUpdated()
            ]);
        $repository = new UserRepository($db->reveal());

        $result = $repository->getUserByEmail($email);

        $this->assertEquals($user, $result);
    }

    /**
     * @covers ::__construct
     * @covers ::createUser
     */
    public function testCreateUser() {
        $email = new Email('test@dummy.com');
        $name = new Name('John Smith');
        $created = new DateTime(date('Y-m-d H:i:s'));
        $user = new User(
            123456,
            $email,
            $name,
            $created
        );

        $db = $this->prophesize(Connection::class);
        $db->insert('user', [
            'email' => $email,
            'full_name' => $name,
            'created' => $created
        ])
            ->willReturn(1);
        $db->lastInsertId()
            ->willReturn($user->getId());
        $repository = new UserRepository($db->reveal());

        $result = $repository->createUser($email, $name);

        $this->assertEquals($user, $result);
    }

    /**
     * @covers ::__construct
     * @covers ::updateUser
     */
    public function testUpdateUser() {
        $email = new Email('test@dummy.com');
        $name = new Name('John Smith');
        $user = new User(
            123456,
            $email,
            $name,
            new DateTime(date('Y-m-d H:i:s')),
            new DateTime(date('Y-m-d H:i:s'))
        );

        $db = $this->prophesize(Connection::class);
        $db->update('user', [
            'full_name' => (string) $user->getFullName(),
            'updated' => (new \DateTime())->format('Y-m-d H:i:s')
        ], [
            'id' => $user->getId()
        ])
            ->willReturn(1);
        $repository = new UserRepository($db->reveal());

        $result = $repository->updateUser($user, $name);

        $this->assertEquals($user, $result);
    }
}
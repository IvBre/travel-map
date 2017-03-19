<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 6:36 PM
 */

namespace TravelMap\Test\Repository;

use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\Connection;
use TravelMap\Entity\User;
use TravelMap\Repository\UserRepository;
use TravelMap\ValueObject\AccessToken;
use TravelMap\ValueObject\Email;
use TravelMap\ValueObject\Name;

/**
 * @coversDefaultClass TravelMap\Repository\UserRepository::__construct
 */
final class UserRepositoryTest extends TestCase {

    /**
     * @covers ::__construct
     * @covers ::createUser
     */
    public function testCreateUser() {
        $user = new User(
            null,
            new AccessToken('alksdjalkj43kl5h4kj56hj45'),
            new Email('test@dummy.com'),
            new Name('John'),
            new Name('Smith')
        );

        $db = $this->prophesize(Connection::class);
        $db->insert('user', [
            'email' => $user->getEmail(),
            'access_token' => $user->getAccessToken(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
        ])
            ->willReturn(1);
        $repository = new UserRepository($db->reveal());

        $result = $repository->createUser($user);

        $this->assertSame(1, $result);
    }

    /**
     * @covers ::__construct
     * @covers ::updateUser
     */
    public function testUpdateUser() {
        $user = new User(
            123456,
            new AccessToken('alksdjalkj43kl5h4kj56hj45'),
            new Email('test@dummy.com'),
            new Name('John'),
            new Name('Smith')
        );

        $db = $this->prophesize(Connection::class);
        $db->update('user', [
            'access_token' => $user->getAccessToken(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'updated' => $user->getUpdated()
        ], [
            'id' => $user->getId()
        ])
            ->willReturn(1);
        $repository = new UserRepository($db->reveal());

        $result = $repository->updateUser($user);

        $this->assertSame(1, $result);
    }
}
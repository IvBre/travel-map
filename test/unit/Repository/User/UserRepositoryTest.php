<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 6:36 PM
 */

namespace TravelMap\Test\Repository\User;

use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\Connection;
use Prophecy\Argument;
use TravelMap\Entity\User;
use TravelMap\Repository\User\UserRepository;
use TravelMap\ValueObject\DateTime;
use TravelMap\ValueObject\Email;
use TravelMap\ValueObject\Name;

/**
 * @coversDefaultClass TravelMap\Repository\User\UserRepository
 */
final class UserRepositoryTest extends TestCase {

    /**
     * @covers ::__construct
     * @covers ::getUserByEmail
     */
    public function testGetUserByEmailNull() {

        $email = new Email('test@dummy.com');

        $db = $this->prophesize(Connection::class);
        $db->fetchAssoc(Argument::any(), [ $email ])
            ->willReturn([]);
        $repository = new UserRepository($db->reveal());

        $result = $repository->getUserByEmail($email);

        $this->assertNull($result);
    }

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
     * @param int $id
     * @param Email $email
     * @param Name $name
     * @covers ::__construct
     * @covers ::createUser
     * @dataProvider getUsers
     */
    public function testCreateUser($id, $email, $name) {
        $db = $this->prophesize(Connection::class);
        $db->insert('user', Argument::any())
            ->willReturn(1);
        $db->lastInsertId()
            ->willReturn($id);
        $repository = new UserRepository($db->reveal());

        $result = $repository->createUser($email, $name);

        $this->assertEquals($id, $result->getId());
        $this->assertEquals($email, $result->getEmail());
        $this->assertEquals($name, $result->getFullName());
    }

    /**
     * @param int $id
     * @param Email $email
     * @param Name $name
     * @covers ::__construct
     * @covers ::updateUser
     * @dataProvider getUsers
     */
    public function testUpdateUser($id, $email, $name) {

        $user = new User(
            $id,
            $email,
            $name,
            new DateTime(date('Y-m-d H:i:s'))
        );

        $db = $this->prophesize(Connection::class);
        $db->update('user', Argument::any(), [
            'id' => $user->getId()
        ])
            ->willReturn(1);
        $repository = new UserRepository($db->reveal());

        $result = $repository->updateUser($user, $name);

        $this->assertSame($user->getId(), $result->getId());
        $this->assertSame($user->getEmail(), $result->getEmail());
        $this->assertSame($user->getFullName(), $result->getFullName());
    }

    /**
     * @covers ::__construct
     * @covers ::getUserByShareToken
     * @param int $userId
     * @param Email $email
     * @param Name $name
     * @dataProvider getUsers
     */
    public function testGetUserByShareToken($userId, $email, $name) {
        $token = 'lkjlk4j53lk5jjkl';

        $user = new User(
            $userId,
            $email,
            $name,
            new DateTime(date('Y-m-d H:i:s')),
            new DateTime(date('Y-m-d H:i:s'))
        );

        $db = $this->prophesize(Connection::class);
        $db->fetchAssoc(Argument::any(), [ $token ])
            ->willReturn([
                'id' => $user->getId(),
                'email' => (string) $user->getEmail(),
                'full_name' => (string) $user->getFullName(),
                'created' => (string) $user->getCreated(),
                'updated' => (string) $user->getUpdated()
            ]);
        $repository = new UserRepository($db->reveal());

        $result = $repository->getUserByShareToken($token);

        $this->assertEquals($user, $result);
    }

    /**
     * @covers ::__construct
     * @covers ::getUserByShareToken
     */
    public function testGetUserByShareTokenEmpty() {
        $token = 'lkjlk4j53lk5jjkl';

        $db = $this->prophesize(Connection::class);
        $db->fetchAssoc(Argument::any(), [ $token ])
            ->willReturn(false);
        $repository = new UserRepository($db->reveal());

        $result = $repository->getUserByShareToken($token);

        $this->assertNull($result);
    }

    /**
     * @covers ::__construct
     * @covers ::getShareTokenByUserId
     * @param int $userId
     * @dataProvider getUsers
     */
    public function testGetShareTokenByUserId($userId) {
        $token = 'kl34j5lk34j5lk43';
        $db = $this->prophesize(Connection::class);
        $db->fetchAssoc(Argument::any(), [ $userId ])
            ->willReturn([
                'share_token' => $token
            ]);
        $repository = new UserRepository($db->reveal());
        $actual = $repository->getShareTokenByUserId($userId);

        $this->assertSame($token, $actual);
    }

    /**
     * @covers ::__construct
     * @covers ::getShareTokenByUserId
     * @param int $userId
     * @dataProvider getUsers
     */
    public function testGetShareTokenByUserIdNew($userId) {
        $db = $this->prophesize(Connection::class);
        $db->fetchAssoc(Argument::any(), [ $userId ])
            ->willReturn(false);
        $db->update(Argument::cetera())
            ->shouldBeCalled(1);
        $repository = new UserRepository($db->reveal());
        $actual = $repository->getShareTokenByUserId($userId);

        $this->assertTrue(is_string($actual));
    }

    public function getUsers() {
        return [
            [
                123456,
                new Email('john.smith@test.com'),
                new Name('John Smith')
            ],
            [
                45678,
                new Email('petar.petrovic@test.com'),
                new Name('Petar Petrovic')
            ]
        ];
    }
}
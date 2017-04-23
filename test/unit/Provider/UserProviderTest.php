<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 4/23/17
 * Time: 2:41 PM
 */

namespace TravelMap\Provider;

use OAuth\Common\Token\TokenInterface;
use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\Connection;
use Prophecy\Argument;
use Symfony\Component\Security\Core\User\User;
use TravelMap\Entity\OAuthToken;
use TravelMap\Repository\OAuthToken\OAuthTokenRepository;
use TravelMap\Repository\User\UserRepository;
use TravelMap\ValueObject\DateTime;
use TravelMap\ValueObject\Email;
use TravelMap\ValueObject\Name;

/**
 * @coversDefaultClass \TravelMap\Provider\UserProvider
 */
class UserProviderTest extends TestCase {

    /**
     * @covers ::__construct
     * @covers ::loadUserByUsername
     * @param int $userId
     * @param string $name
     * @param string $email
     * @dataProvider getUsersProvider
     */
    public function testLoadUserByUsername($userId, $name, $email) {
        $db = $this->prophesize(Connection::class);
        $db->fetchAssoc(Argument::cetera())
            ->willReturn([
                'id' => $userId,
                'full_name' => $name,
                'created' => date('Y-m-d H:i:s', strtotime('-1 month')),
                'updated' => date('Y-m-d H:i:s'),
            ]);
        $userRepository = new UserRepository($db->reveal());

        $db = $this->prophesize(Connection::class);
        $db->fetchAssoc(Argument::cetera())
            ->willReturn([
                'id' => 123567,
                'service' => 'Google',
                'access_token' => 'keji34h5k3h45kjh3jk4h5',
                'refresh_token' => 'nmbwm5b34h53g5hj3g4hj5',
                'expires_on' => date('Y-m-d H:i:s', strtotime('+7 days')),
                'created' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'last_time_used' => date('Y-m-d H:i:s'),
            ]);
        $oAuthTokenRepository = new OAuthTokenRepository($db->reveal());

        $userProvider = new UserProvider($userRepository, $oAuthTokenRepository);

        $user = $userProvider->loadUserByUsername(new Email($email));

        $this->assertSame((string)$user->getFullName(), $name);
    }

    /**
     * @covers ::__construct
     * @covers ::loadUserByUsername
     * @param int $userId
     * @param string $name
     * @param string $email
     * @dataProvider getUsersProvider
     * @expectedException \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function testLoadUserByUsernameException($userId, $name, $email) {
        $db = $this->prophesize(Connection::class);
        $db->fetchAssoc(Argument::cetera())
            ->willReturn(false);
        $userRepository = new UserRepository($db->reveal());

        $db = $this->prophesize(Connection::class);
        $oAuthTokenRepository = new OAuthTokenRepository($db->reveal());

        $userProvider = new UserProvider($userRepository, $oAuthTokenRepository);

        $userProvider->loadUserByUsername(new Email($email));
    }

    /**
     * @covers ::__construct
     * @covers ::loadUserByOAuthCredentials
     * @param int $userId
     * @param string $name
     * @param string $email
     * @dataProvider getUsersProvider
     */
    public function testLoadUserByOAuthCredentials($userId, $name, $email) {
        $db = $this->prophesize(Connection::class);
        $db->fetchAssoc(Argument::cetera())
            ->willReturn([
                'id' => $userId,
                'full_name' => $name,
                'created' => date('Y-m-d H:i:s', strtotime('-1 month')),
                'updated' => date('Y-m-d H:i:s'),
            ]);
        $db->update(Argument::cetera())
            ->shouldBeCalled(1);
        $userRepository = new UserRepository($db->reveal());

        $db = $this->prophesize(Connection::class);
        $db->fetchAssoc(Argument::cetera())
            ->willReturn([
                'id' => 123567,
                'service' => 'Google',
                'access_token' => 'keji34h5k3h45kjh3jk4h5',
                'refresh_token' => 'nmbwm5b34h53g5hj3g4hj5',
                'expires_on' => date('Y-m-d H:i:s', strtotime('+7 days')),
                'created' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'last_time_used' => date('Y-m-d H:i:s'),
            ]);
        $db->update(Argument::cetera())
            ->shouldBeCalled(1);
        $oAuthTokenRepository = new OAuthTokenRepository($db->reveal());

        $userProvider = new UserProvider($userRepository, $oAuthTokenRepository);

        $oAuthToken = new \Gigablah\Silex\OAuth\Security\Authentication\Token\OAuthToken('google');
        $oAuthToken->setEmail($email);
        $oAuthToken->setUser($name);
        $accessToken = $this->prophesize(TokenInterface::class);
        $accessToken->getEndOfLife()
            ->willReturn(strtotime('+7 days'));
        $accessToken->getAccessToken()
            ->willReturn('keji34h5k3h45kjh3jk4h5');
        $accessToken->getRefreshToken()
            ->willReturn('nmbwm5b34h53g5hj3g4hj5');
        $oAuthToken->setAccessToken($accessToken->reveal());
        $oAuthToken->setService('Google');

        $user = $userProvider->loadUserByOAuthCredentials($oAuthToken);

        $this->assertSame((string)$user->getFullName(), $name);
        $this->assertInstanceOf(OAuthToken::class, $user->getOAuth());
    }

    /**
     * @covers ::__construct
     * @covers ::loadUserByOAuthCredentials
     * @param int $userId
     * @param string $name
     * @param string $email
     * @dataProvider getUsersProvider
     */
    public function testLoadUserByOAuthCredentialsNew($userId, $name, $email) {
        $db = $this->prophesize(Connection::class);
        $db->fetchAssoc(Argument::cetera())
            ->willReturn(false);
        $db->insert(Argument::cetera())
            ->shouldBeCalled(1);
        $db->lastInsertId()
            ->willReturn($userId);
        $userRepository = new UserRepository($db->reveal());

        $db = $this->prophesize(Connection::class);
        $db->fetchAssoc(Argument::cetera())
            ->willReturn(false);
        $db->insert(Argument::cetera())
            ->shouldBeCalled(1);
        $db->lastInsertId()
            ->shouldBeCalled(1);
        $oAuthTokenRepository = new OAuthTokenRepository($db->reveal());

        $userProvider = new UserProvider($userRepository, $oAuthTokenRepository);

        $oAuthToken = new \Gigablah\Silex\OAuth\Security\Authentication\Token\OAuthToken('google');
        $oAuthToken->setEmail($email);
        $oAuthToken->setUser($name);
        $accessToken = $this->prophesize(TokenInterface::class);
        $accessToken->getEndOfLife()
            ->willReturn(strtotime('+7 days'));
        $accessToken->getAccessToken()
            ->willReturn('keji34h5k3h45kjh3jk4h5');
        $accessToken->getRefreshToken()
            ->willReturn('nmbwm5b34h53g5hj3g4hj5');
        $oAuthToken->setAccessToken($accessToken->reveal());
        $oAuthToken->setService('Google');

        $user = $userProvider->loadUserByOAuthCredentials($oAuthToken);

        $this->assertSame((string)$user->getFullName(), $name);
        $this->assertInstanceOf(OAuthToken::class, $user->getOAuth());
    }

    /**
     * @covers ::__construct
     * @covers ::refreshUser
     * @param int $userId
     * @param string $name
     * @param string $email
     * @dataProvider getUsersProvider
     * @expectedException \Symfony\Component\Security\Core\Exception\UnsupportedUserException
     */
    public function testRefreshUserException($userId, $name, $email) {
        $db = $this->prophesize(Connection::class);
        $userRepository = new UserRepository($db->reveal());
        $oAuthTokenRepository = new OAuthTokenRepository($db->reveal());

        $userProvider = new UserProvider($userRepository, $oAuthTokenRepository);
        $user = new User($email, '');
        $userProvider->refreshUser($user);
    }

    /**
     * @covers ::__construct
     * @covers ::refreshUser
     * @param int $userId
     * @param string $name
     * @param string $email
     * @dataProvider getUsersProvider
     */
    public function testRefreshUser($userId, $name, $email) {
        $db = $this->prophesize(Connection::class);
        $db->fetchAssoc(Argument::cetera())
            ->willReturn([
                'id' => $userId,
                'full_name' => $name,
                'created' => date('Y-m-d H:i:s', strtotime('-1 month')),
                'updated' => date('Y-m-d H:i:s'),
            ]);
        $userRepository = new UserRepository($db->reveal());

        $db = $this->prophesize(Connection::class);
        $db->fetchAssoc(Argument::cetera())
            ->willReturn([
                'id' => 123567,
                'service' => 'Google',
                'access_token' => 'keji34h5k3h45kjh3jk4h5',
                'refresh_token' => 'nmbwm5b34h53g5hj3g4hj5',
                'expires_on' => date('Y-m-d H:i:s', strtotime('+7 days')),
                'created' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'last_time_used' => date('Y-m-d H:i:s'),
            ]);
        $oAuthTokenRepository = new OAuthTokenRepository($db->reveal());

        $userProvider = new UserProvider($userRepository, $oAuthTokenRepository);
        $user = new \TravelMap\Entity\User($userId, new Email($email), new Name($name), new DateTime(date('Y-m-d H:i:s')));
        $refreshedUser = $userProvider->refreshUser($user);

        $this->assertSame((string)$refreshedUser->getFullName(), $name);
    }

    /**
     * @covers ::__construct
     * @covers ::supportsClass
     */
    public function testSupportsClass() {
        $db = $this->prophesize(Connection::class);
        $userRepository = new UserRepository($db->reveal());
        $oAuthTokenRepository = new OAuthTokenRepository($db->reveal());

        $userProvider = new UserProvider($userRepository, $oAuthTokenRepository);
        $supports = $userProvider->supportsClass(\TravelMap\Entity\User::class);

        $this->assertTrue($supports);
    }

    public function getUsersProvider() {
        return [
            [
                123456,
                'John Smith',
                'john.smith@test.com'
            ],
            [
                876543,
                'Petar Petrovic',
                'petar.petrovic@test.com'
            ]
        ];
    }
}
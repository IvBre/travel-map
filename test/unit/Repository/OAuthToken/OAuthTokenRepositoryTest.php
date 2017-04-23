<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 4/23/17
 * Time: 3:55 PM
 */

namespace TravelMap\Test\Repository\OAuthToken;

use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\Connection;
use Prophecy\Argument;
use TravelMap\Entity\OAuthToken;
use TravelMap\Repository\OAuthToken\OAuthTokenRepository;
use OAuth\Common\Token\TokenInterface;
use TravelMap\ValueObject\AccessToken;
use TravelMap\ValueObject\DateTime;
use TravelMap\ValueObject\Service;

/**
 * @coversDefaultClass \TravelMap\Repository\OAuthToken\OAuthTokenRepository
 */
class OAuthTokenRepositoryTest extends TestCase {

    /**
     * @param int $userId
     * @param string $email
     * @param string $name
     * @param string $token
     * @dataProvider getUserDataProvider
     */
    public function testCreateOAuthToken($userId, $email, $name, $token) {
        $db = $this->prophesize(Connection::class);
        $repository = new OAuthTokenRepository($db->reveal());

        $oAuthToken = new \Gigablah\Silex\OAuth\Security\Authentication\Token\OAuthToken('google');
        $oAuthToken->setEmail($email);
        $oAuthToken->setUser($name);
        $accessToken = $this->prophesize(TokenInterface::class);
        $accessToken->getEndOfLife()
            ->willReturn(strtotime('+7 days'));
        $accessToken->getAccessToken()
            ->willReturn($token);
        $accessToken->getRefreshToken()
            ->willReturn('nmbwm5b34h53g5hj3g4hj5');
        $oAuthToken->setAccessToken($accessToken->reveal());
        $oAuthToken->setService('Google');

        $actual = $repository->createOAuthToken($userId, $oAuthToken);

        $this->assertSame($token, $actual->getAccessToken()->getAccessToken());
    }

    /**
     * @covers ::__construct
     * @covers ::updateOAuthToken
     * @param int $userId
     * @param string $email
     * @param string $name
     * @param string $token
     * @dataProvider getUserDataProvider
     */
    public function testUpdateOAuthToken($userId, $email, $name, $token) {
        $db = $this->prophesize(Connection::class);
        $repository = new OAuthTokenRepository($db->reveal());

        $oAuthToken = new OAuthToken(
            123456,
            $userId,
            new Service('Google'),
            new AccessToken($token),
            new DateTime(date('Y-m-d H:i:s', strtotime('+7 days'))),
            new DateTime(date('Y-m-d H:i:s')));

        $actual = $repository->updateOAuthToken($userId, $oAuthToken);

        $this->assertSame($token, (string)$actual->getAccessToken());
    }

    /**
     * @covers ::__construct
     * @covers ::getOAuthToken
     * @param int $userId
     * @param string $email
     * @param string $name
     * @param string $token
     * @dataProvider getUserDataProvider
     */
    public function testGetOAuthToken($userId, $email, $name, $token) {
        $db = $this->prophesize(Connection::class);
        $db->fetchAssoc(Argument::cetera())
            ->willReturn([
                'id' => 234353,
                'service' => 'Google',
                'created' => date('Y-m-d h:i:s')
            ]);
        $repository = new OAuthTokenRepository($db->reveal());

        $oAuthToken = new \Gigablah\Silex\OAuth\Security\Authentication\Token\OAuthToken('google');
        $oAuthToken->setEmail($email);
        $oAuthToken->setUser($name);
        $accessToken = $this->prophesize(TokenInterface::class);
        $accessToken->getEndOfLife()
            ->willReturn(strtotime('+7 days'));
        $accessToken->getAccessToken()
            ->willReturn($token);
        $accessToken->getRefreshToken()
            ->willReturn('nmbwm5b34h53g5hj3g4hj5');
        $oAuthToken->setAccessToken($accessToken->reveal());
        $oAuthToken->setService('Google');

        $actual = $repository->getOAuthToken($userId, $oAuthToken);

        $this->assertSame($token, (string)$actual->getAccessToken());
    }

    /**
     * @covers ::__construct
     * @covers ::getOAuthToken
     * @param int $userId
     * @param string $email
     * @param string $name
     * @param string $token
     * @dataProvider getUserDataProvider
     */
    public function testGetOAuthTokenEmptyFreshToken($userId, $email, $name, $token) {
        $db = $this->prophesize(Connection::class);
        $db->fetchAssoc(Argument::cetera())
            ->willReturn([
                'id' => 234353,
                'service' => 'Google',
                'created' => date('Y-m-d h:i:s')
            ]);
        $query = <<<SQL
SELECT refresh_token 
FROM oauth_token 
WHERE service = ? 
  AND user_id = ? 
  AND refresh_token IS NOT NULL 
ORDER BY expires_on DESC
SQL;
        $db->fetchAssoc($query, Argument::cetera())
            ->willreturn([
                'refresh_token' => $token
            ]);
        $repository = new OAuthTokenRepository($db->reveal());

        $oAuthToken = new \Gigablah\Silex\OAuth\Security\Authentication\Token\OAuthToken('google');
        $oAuthToken->setEmail($email);
        $oAuthToken->setUser($name);
        $accessToken = $this->prophesize(TokenInterface::class);
        $accessToken->getEndOfLife()
            ->willReturn(strtotime('+7 days'));
        $accessToken->getAccessToken()
            ->willReturn($token);
        $accessToken->getRefreshToken()
            ->willReturn(null);
        $oAuthToken->setAccessToken($accessToken->reveal());
        $oAuthToken->setService('Google');

        $actual = $repository->getOAuthToken($userId, $oAuthToken);

        $this->assertSame($token, (string)$actual->getAccessToken());
    }

    /**
     * @covers ::__construct
     * @covers ::getOAuthToken
     * @param int $userId
     * @param string $email
     * @param string $name
     * @param string $token
     * @dataProvider getUserDataProvider
     */
    public function testGetOAuthTokenEmpty($userId, $email, $name, $token) {
        $db = $this->prophesize(Connection::class);
        $db->fetchAssoc(Argument::cetera())
            ->willReturn(false);
        $repository = new OAuthTokenRepository($db->reveal());

        $oAuthToken = new \Gigablah\Silex\OAuth\Security\Authentication\Token\OAuthToken('google');
        $oAuthToken->setEmail($email);
        $oAuthToken->setUser($name);
        $accessToken = $this->prophesize(TokenInterface::class);
        $oAuthToken->setAccessToken($accessToken->reveal());
        $oAuthToken->setService('Google');

        $actual = $repository->getOAuthToken($userId, $oAuthToken);

        $this->assertNull($actual);
    }

    /**
     * @covers ::__construct
     * @covers ::getLastUsedOAuthToken
     * @param int $userId
     * @param string $email
     * @param string $name
     * @param string $token
     * @dataProvider getUserDataProvider
     */
    public function testGetLastUsedOAuthToken($userId, $email, $name, $token) {
        $db = $this->prophesize(Connection::class);
        $db->fetchAssoc(Argument::cetera())
            ->willReturn([
                'id' => 1232343,
                'service' => 'Google',
                'access_token' => $token,
                'expires_on' => date('Y-m-d H:i:s', strtotime('+7 days')),
                'created' => date('Y-m-d H:i:s'),
                'last_time_used' => date('Y-m-d H:i:s'),
                'refresh_token' => $token
            ]);
        $repository = new OAuthTokenRepository($db->reveal());
        $actual = $repository->getLastUsedOAuthToken($userId);

        $this->assertSame($token, (string)$actual->getRefreshToken());
    }

    /**
     * @covers ::__construct
     * @covers ::getLastUsedOAuthToken
     * @param int $userId
     * @dataProvider getUserDataProvider
     * @expectedException \Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException
     */
    public function testGetLastUsedOAuthTokenEmpty($userId) {
        $db = $this->prophesize(Connection::class);
        $db->fetchAssoc(Argument::cetera())
            ->willReturn(false);
        $repository = new OAuthTokenRepository($db->reveal());
        $repository->getLastUsedOAuthToken($userId);
    }

    /**
     * @covers ::__construct
     * @covers ::getLastUsedOAuthToken
     * @param int $userId
     * @param string $email
     * @param string $name
     * @param string $token
     * @dataProvider getUserDataProvider
     */
    public function testGetLastUsedOAuthTokenNoRefreshToken($userId, $email, $name, $token) {
        $db = $this->prophesize(Connection::class);
        $db->fetchAssoc(Argument::cetera())
            ->willReturn([
                'id' => 1232343,
                'service' => 'Google',
                'access_token' => $token,
                'expires_on' => date('Y-m-d H:i:s', strtotime('+7 days')),
                'created' => date('Y-m-d H:i:s'),
                'last_time_used' => date('Y-m-d H:i:s'),
                'refresh_token' => null
            ]);

        $query = <<<SQL
SELECT refresh_token 
FROM oauth_token 
WHERE service = ? 
  AND user_id = ? 
  AND refresh_token IS NOT NULL 
ORDER BY expires_on DESC
SQL;
        $db->fetchAssoc($query, Argument::cetera())
            ->willreturn([
                'refresh_token' => $token
            ]);

        $repository = new OAuthTokenRepository($db->reveal());
        $actual = $repository->getLastUsedOAuthToken($userId);

        $this->assertSame($token, (string)$actual->getRefreshToken());
    }

    /**
     * @covers ::__construct
     * @covers ::getLastRefreshToken
     * @param int $userId
     * @param string $email
     * @param string $name
     * @param string $token
     * @dataProvider getUserDataProvider
     */
    public function testGetLastRefreshToken($userId, $email, $name, $token) {
        $db = $this->prophesize(Connection::class);
        $db->fetchAssoc(Argument::cetera())
            ->willReturn([
                'refresh_token' => $token
            ]);
        $repository = new OAuthTokenRepository($db->reveal());
        $actual = $repository->getLastRefreshToken($userId, 'Google');

        $this->assertSame($token, (string)$actual->getAccessToken());
    }

    /**
     * @covers ::__construct
     * @covers ::getLastRefreshToken
     * @param int $userId
     * @dataProvider getUserDataProvider
     * @expectedException \Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException
     */
    public function testGetLastRefreshTokenException($userId) {
        $db = $this->prophesize(Connection::class);
        $db->fetchAssoc(Argument::cetera())
            ->willReturn(false);
        $repository = new OAuthTokenRepository($db->reveal());
        $repository->getLastRefreshToken($userId, 'Google');
    }

    public function getUserDataProvider() {
        return [
            [
                123456,
                'john.smith@test.com',
                'John Smith',
                'ksjdfsdf789sdf7fsfsdfnjk34'
            ],
            [
                9897546,
                'petar.petrovic@test.com',
                'Petar Petrovic',
                'sfds8df7d8fg76df78g6786d6fg'
            ]
        ];
    }
}
<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 4/16/17
 * Time: 7:44 AM
 */

namespace TravelMap\Entity;

use PHPUnit\Framework\TestCase;
use TravelMap\ValueObject\AccessToken;
use TravelMap\ValueObject\DateTime;
use TravelMap\ValueObject\Service;

/**
 * @coversDefaultClass \TravelMap\Entity\OAuthToken
 */
class OAuthTokenTest extends TestCase {

    /**
     * @param int $id
     * @param int $userId
     * @param Service $service
     * @param AccessToken $accessToken
     * @param DateTime $expiresOn
     * @param DateTime $created
     *
     * @covers ::__construct
     * @covers ::getId
     * @dataProvider getOauthTokenAttributes
     */
    public function testGetId($id, $userId, $service, $accessToken, $expiresOn, $created) {
        $entity = new OAuthToken($id, $userId, $service, $accessToken, $expiresOn, $created);
        $actual = $entity->getId();

        $this->assertSame($id, $actual);
    }

    /**
     * @param int $id
     * @param int $userId
     * @param Service $service
     * @param AccessToken $accessToken
     * @param DateTime $expiresOn
     * @param DateTime $created
     *
     * @covers ::__construct
     * @covers ::getService
     * @dataProvider getOauthTokenAttributes
     */
    public function testGetService($id, $userId, $service, $accessToken, $expiresOn, $created) {
        $entity = new OAuthToken($id, $userId, $service, $accessToken, $expiresOn, $created);
        $actual = $entity->getService();

        $this->assertSame($service, $actual);
    }

    /**
     * @param int $id
     * @param int $userId
     * @param Service $service
     * @param AccessToken $accessToken
     * @param DateTime $expiresOn
     * @param DateTime $created
     *
     * @covers ::__construct
     * @covers ::getAccessToken
     * @dataProvider getOauthTokenAttributes
     */
    public function testGetAccessToken($id, $userId, $service, $accessToken, $expiresOn, $created) {
        $entity = new OAuthToken($id, $userId, $service, $accessToken, $expiresOn, $created);
        $actual = $entity->getAccessToken();

        $this->assertSame($accessToken, $actual);
    }

    /**
     * @param int $id
     * @param int $userId
     * @param Service $service
     * @param AccessToken $accessToken
     * @param DateTime $expiresOn
     * @param DateTime $created
     * @param AccessToken $refreshToken
     *
     * @covers ::__construct
     * @covers ::getRefreshToken
     * @dataProvider getOauthTokenAttributes
     */
    public function testGetRefreshToken($id, $userId, $service, $accessToken, $expiresOn, $created, $refreshToken) {
        $entity = new OAuthToken($id, $userId, $service, $accessToken, $expiresOn, $created, null, $refreshToken);
        $actual = $entity->getRefreshToken();

        $this->assertSame($refreshToken, $actual);
    }

    /**
     * @param int $id
     * @param int $userId
     * @param Service $service
     * @param AccessToken $accessToken
     * @param DateTime $expiresOn
     * @param DateTime $created
     *
     * @covers ::__construct
     * @covers ::getExpiresOn
     * @dataProvider getOauthTokenAttributes
     */
    public function testGetExpiresOn($id, $userId, $service, $accessToken, $expiresOn, $created) {
        $entity = new OAuthToken($id, $userId, $service, $accessToken, $expiresOn, $created);
        $actual = $entity->getExpiresOn();

        $this->assertSame($expiresOn, $actual);
    }

    /**
     * @param int $id
     * @param int $userId
     * @param Service $service
     * @param AccessToken $accessToken
     * @param DateTime $expiresOn
     * @param DateTime $created
     *
     * @covers ::__construct
     * @covers ::getCreated
     * @dataProvider getOauthTokenAttributes
     */
    public function testGetCreated($id, $userId, $service, $accessToken, $expiresOn, $created) {
        $entity = new OAuthToken($id, $userId, $service, $accessToken, $expiresOn, $created);
        $actual = $entity->getCreated();

        $this->assertSame($created, $actual);
    }

    /**
     * @param int $id
     * @param int $userId
     * @param Service $service
     * @param AccessToken $accessToken
     * @param DateTime $expiresOn
     * @param DateTime $created
     *
     * @covers ::__construct
     * @covers ::getLastTimeUsed
     * @dataProvider getOauthTokenAttributes
     */
    public function testGetLastTimeUsed($id, $userId, $service, $accessToken, $expiresOn, $created) {
        $lastTimeUsed = new DateTime(date('Y-m-d H:i:s'));
        $entity = new OAuthToken($id, $userId, $service, $accessToken, $expiresOn, $created, $lastTimeUsed);
        $actual = $entity->getLastTimeUsed();

        $this->assertSame($lastTimeUsed, $actual);
    }

    /**
     * @param int $id
     * @param int $userId
     * @param Service $service
     * @param AccessToken $accessToken
     * @param AccessToken $refreshToken
     * @param DateTime $expiresOn
     * @param DateTime $created
     *
     * @covers ::__construct
     * @covers ::getCredentials
     * @dataProvider getOauthTokenAttributes
     */
    public function testGetCredentials($id, $userId, $service, $accessToken, $expiresOn, $created, $refreshToken) {
        $now = new \DateTime();
        $diff = $now->diff($expiresOn->getDateTime());

        $expected = [
            'access_token' => (string) $accessToken,
            'refresh_token' => (string) $refreshToken,
            'expires_in' => $diff->format('U'),
            'expires_on' => (string) $expiresOn,
            'issued_at' => (string) $created,
        ];

        $entity = new OAuthToken($id, $userId, $service, $accessToken, $expiresOn, $created, null, $refreshToken);
        $actual = $entity->getCredentials();

        $this->assertSame($expected, $actual);
    }

    public function getOauthTokenAttributes() {
        return [
            [
                123,
                555,
                new Service('google'),
                new AccessToken('lk3j45k4hjk6456j5g67'),
                new DateTime(date('Y-m-d H:i:s', strtotime('+7 days'))),
                new DateTime(date('Y-m-d H:i:s')),
                null
            ],
            [
                456,
                666,
                new Service('facebook'),
                new AccessToken('dslkfjsdlkfhl4k35k3h4k'),
                new DateTime(date('Y-m-d H:i:s', strtotime('+7 days'))),
                new DateTime(date('Y-m-d H:i:s')),
                new AccessToken('aspp345o3p45o3p45op345')
            ]
        ];
    }
}
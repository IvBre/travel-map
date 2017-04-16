<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 4/16/17
 * Time: 8:39 AM
 */

namespace TravelMap\ValueObject;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \TravelMap\ValueObject\AccessToken
 */
class AccessTokenTest extends TestCase {

    /**
     * @param string $token
     * @covers ::__construct
     * @covers ::getAccessToken
     * @dataProvider getAccessTokens
     */
    public function testGetAccessToken($token) {
        $object = new AccessToken($token);
        $actual = $object->getAccessToken();

        $this->assertSame($token, $actual);
    }

    /**
     * @param string $token
     * @covers ::__construct
     * @covers ::__toString
     * @dataProvider getAccessTokens
     */
    public function testToString($token) {
        $object = new AccessToken($token);
        $actual = (string)$object;

        $this->assertSame((string)$token, $actual);
    }

    public function getAccessTokens() {
        return [
            [
                'l34j5lk3j45lk34h53jk4h6'
            ],
            [
                'lkjkj45hk65jh7kh5g675g6'
            ]
        ];
    }
}
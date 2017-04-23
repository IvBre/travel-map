<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 4/23/17
 * Time: 2:33 PM
 */

namespace TravelMap\Factory;

use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\Connection;
use Prophecy\Argument;
use TravelMap\Repository\OAuthToken\OAuthTokenRepository;

/**
 * @coversDefaultClass \TravelMap\Factory\GoogleServiceFactory
 */
class GoogleServiceFactoryTest extends TestCase  {

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreate() {
        $config = [
            'client_id' => 'kh45k34hj5kj34h6',
            'client_secret' => 'klh45kj6h5kj6hkj',
        ];

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

        $factory = new GoogleServiceFactory($config, $oAuthTokenRepository);
        $service = $factory->create(123456);

        $this->assertInstanceOf(\Google_Service_Calendar::class, $service);
    }
}
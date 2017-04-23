<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 4/23/17
 * Time: 3:19 PM
 */

namespace TravelMap\CoordinatesResolver;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

/**
 * @coversDefaultClass \TravelMap\CoordinatesResolver\GoogleCoordinatesResolver
 */
class GoogleCoordinatesResolverTest extends TestCase {

    /**
     * @covers ::__construct
     * @covers ::resolve
     * @param string $address
     * @param float $lat
     * @param float $lng
     * @dataProvider getLocationDataProvider
     */
    public function testResolve($address, $lat, $lng) {
        $response = new Response(200, [], \json_encode([
            'status' => 'OK',
            'results' => [
                [
                    'geometry' => [
                        'location' => [
                            'lat' => $lat,
                            'lng' => $lng
                        ]
                    ]
                ]
            ]
        ]));
        $client = $this->prophesize(ClientInterface::class);
        $client->request(Argument::cetera())
            ->willReturn($response);

        $resolver = new GoogleCoordinatesResolver([
            'api_key' => 'lkj5lk6j45k65h7'
        ], $client->reveal());

        $result = $resolver->resolve($address);

        $this->assertSame($result->getLatitude(), $lat);
        $this->assertSame($result->getLongitude(), $lng);
    }

    /**
     * @covers ::__construct
     * @covers ::resolve
     * @param string $address
     * @param float $lat
     * @param float $lng
     * @dataProvider getLocationDataProvider
     */
    public function testResolveInvalidStatus($address, $lat, $lng) {
        $response = new Response(400);
        $client = $this->prophesize(ClientInterface::class);
        $client->request(Argument::cetera())
            ->willReturn($response);

        $resolver = new GoogleCoordinatesResolver([
            'api_key' => 'lkj5lk6j45k65h7'
        ], $client->reveal());

        $result = $resolver->resolve($address);

        $this->assertFalse($result);
    }

    /**
     * @covers ::__construct
     * @covers ::resolve
     * @param string $address
     * @param float $lat
     * @param float $lng
     * @dataProvider getLocationDataProvider
     */
    public function testResolveInvalidResponse($address, $lat, $lng) {
        $response = new Response(200, [], \json_encode([
            'status' => 'FAILED',
        ]));
        $client = $this->prophesize(ClientInterface::class);
        $client->request(Argument::cetera())
            ->willReturn($response);

        $resolver = new GoogleCoordinatesResolver([
            'api_key' => 'lkj5lk6j45k65h7'
        ], $client->reveal());

        $result = $resolver->resolve($address);

        $this->assertFalse($result);
    }

    public function getLocationDataProvider() {
        return [
            [
                'Berlin, Germany',
                45.68899,
                -125.5675678
            ],
            [
                'Belgrade, Serbia',
                56.768678,
                -139.456567
            ]
        ];
    }
}
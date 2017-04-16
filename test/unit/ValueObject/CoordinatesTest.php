<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 4/16/17
 * Time: 8:46 AM
 */

namespace TravelMap\ValueObject;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \TravelMap\ValueObject\Coordinates
 */
class CoordinatesTest extends TestCase {

    /**
     * @param string $lat
     * @param string $long
     * @covers ::__construct
     * @covers ::getLatitude
     * @dataProvider getCoordinates
     */
    public function testGetLatitude($lat, $long) {
        $object = new Coordinates($lat, $long);
        $actual = $object->getLatitude();

        $this->assertSame($lat, $actual);
    }

    /**
     * @param string $lat
     * @param string $long
     * @covers ::__construct
     * @covers ::getLongitude
     * @dataProvider getCoordinates
     */
    public function testGetLongitude($lat, $long) {
        $object = new Coordinates($lat, $long);
        $actual = $object->getLongitude();

        $this->assertSame($long, $actual);
    }

    public function getCoordinates() {
        return [
            [
                37.42242, -122.08585
            ],
            [
                35.42252, -140.05565
            ]
        ];
    }
}
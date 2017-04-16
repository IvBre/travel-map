<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 4/16/17
 * Time: 9:18 AM
 */

namespace TravelMap\ValueObject;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \TravelMap\ValueObject\Service
 */
class ServiceTest extends TestCase {

    /**
     * @param string $service
     * @dataProvider getServices
     * @covers ::__construct
     * @covers ::getService
     */
    public function testGetService($service) {
        $object = new Service($service);
        $actual = $object->getService();

        $this->assertSame($service, $actual);
    }

    /**
     * @param string $service
     * @dataProvider getServices
     * @covers ::__construct
     * @covers ::__toString
     */
    public function testToString($service) {
        $object = new Service($service);
        $actual = (string)$object;

        $this->assertSame($service, $actual);
    }

    public function getServices() {
        return [
            [
                'google'
            ],
            [
                'facebook'
            ]
        ];
    }
}
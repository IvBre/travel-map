<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 4/16/17
 * Time: 9:14 AM
 */

namespace TravelMap\ValueObject;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \TravelMap\ValueObject\Name
 */
class NameTest extends TestCase {

    /**
     * @param string $name
     * @covers ::__construct
     * @covers ::getName
     * @dataProvider getNames
     */
    public function testGetName($name) {
        $object = new Name($name);
        $actual = $object->getName();

        $this->assertSame($name, $actual);
    }

    /**
     * @param string $name
     * @covers ::__construct
     * @covers ::__toString
     * @dataProvider getNames
     */
    public function testToString($name) {
        $object = new Name($name);
        $actual = (string)$object;

        $this->assertSame($name, $actual);
    }

    public function getNames() {
        return [
            [
                'John Smith'
            ],
            [
                'Petar Petrovic'
            ]
        ];
    }
}
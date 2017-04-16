<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 4/16/17
 * Time: 8:52 AM
 */

namespace TravelMap\ValueObject;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \TravelMap\ValueObject\DateTime
 */
class DateTimeTest extends TestCase {

    /**
     * @expectedException \InvalidArgumentException
     * @covers ::__construct
     */
    public function testConstructFailed() {
        new DateTime(123235435);
    }

    /**
     * @param string $dateTime
     * @covers ::__construct
     * @covers ::getDateTime
     * @dataProvider getDateTimes
     */
    public function testGetDateTime($dateTime) {
        $expected = new \DateTime($dateTime);
        $object = new DateTime($dateTime);
        $actual = $object->getDateTime();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param string $dateTime
     * @covers ::__construct
     * @covers ::__toString
     * @dataProvider getDateTimes
     */
    public function testToString($dateTime) {
        $expected = new \DateTime($dateTime);
        $object = new DateTime($dateTime);
        $actual = (string)$object;

        $this->assertSame($expected->format('Y-m-d H:i:s'), $actual);
    }

    public function getDateTimes() {
        return [
            [
                '2017-04-15 15:00:00'
            ],
            [
                '2017-03-24 10:00:00'
            ]
        ];
    }
}
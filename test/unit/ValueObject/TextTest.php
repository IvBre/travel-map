<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 4/16/17
 * Time: 9:18 AM
 */

namespace TravelMap\ValueObject;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \TravelMap\ValueObject\Text
 */
class TextTest extends TestCase {

    /**
     * @param string $text
     * @dataProvider getTexts
     * @covers ::__construct
     * @covers ::__toString
     */
    public function testToString($text) {
        $object = new Text($text);
        $actual = (string)$object;

        $this->assertSame($text, $actual);
    }

    public function getTexts() {
        return [
            [
                'Lorem Ipsum dolor sit amet'
            ],
            [
                'Bacon ipsum dolor amet drumstick strip steak rump'
            ]
        ];
    }
}
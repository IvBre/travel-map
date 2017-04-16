<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 4/16/17
 * Time: 9:24 AM
 */

namespace TravelMap\ValueObject;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \TravelMap\ValueObject\Url
 */
class UrlTest extends TestCase {

    /**
     * @param string $url
     * @dataProvider getUrls
     * @covers ::__construct
     * @covers ::getUrl
     */
    public function testGetUrl($url) {
        $object = new Url($url);
        $actual = $object->getUrl();

        $this->assertSame($url, $actual);
    }

    /**
     * @param string $url
     * @dataProvider getUrls
     * @covers ::__construct
     * @covers ::__toString
     */
    public function testToString($url) {
        $object = new Url($url);
        $actual = (string)$object;

        $this->assertSame($url, $actual);
    }

    public function getUrls() {
        return [
            [
                'http://google.com/calendar/evet/123456'
            ],
            [
                'http://google.com/calendar/evet/5676789'
            ]
        ];
    }
}
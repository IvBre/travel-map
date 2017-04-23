<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 4/23/17
 * Time: 1:46 PM
 */

namespace TravelMap\CoordinatesResolver;

use TravelMap\ValueObject\Coordinates;

interface CoordinatesResolverInterface {

    /**
     * @param string $address
     * @return Coordinates
     */
    public function resolve($address);
}
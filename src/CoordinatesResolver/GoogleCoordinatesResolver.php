<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 4/23/17
 * Time: 1:47 PM
 */

namespace TravelMap\CoordinatesResolver;

use GuzzleHttp\ClientInterface;
use TravelMap\ValueObject\Coordinates;

final class GoogleCoordinatesResolver implements CoordinatesResolverInterface {

    /** @var array */
    private $authConfig;

    /** @var ClientInterface */
    private $client;

    public function __construct($authConfig, ClientInterface $client) {
        $this->authConfig = $authConfig;
        $this->client = $client;
    }

    /** @inheritdoc */
    public function resolve($address) {
        $address = urlencode($address);
        $url = "https://maps.google.com/maps/api/geocode/json?address=$address&key={$this->authConfig['api_key']}&sensor=false";

        $res = $this->client->request('GET', $url);
        if ($res->getStatusCode() !== 200) {
            return false;
        }

        $response = json_decode($res->getBody());
        if ($response->status !== 'OK') {
            return false;
        }
        $lat  = $response->results[0]->geometry->location->lat;
        $long = $response->results[0]->geometry->location->lng;

        return new Coordinates($lat, $long);
    }
}
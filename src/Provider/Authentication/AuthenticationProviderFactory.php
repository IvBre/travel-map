<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 8:43 PM
 */

namespace TravelMap\Provider\Authentication;

use TravelMap\Exception\NotFoundException;

final class AuthenticationProviderFactory {

    /** @var ProviderInterface[] */
    private $providers = [];

    public function addProvider(ProviderInterface $provider) {
        $this->providers[$provider->getIdentifier()] = $provider;
    }

    /**
     * @param string $identifier
     * @return ProviderInterface
     * @throws NotFoundException
     */
    public function getProviderByIdentifier($identifier) {
        if (!$this->providers[$identifier]) {
            throw new NotFoundException("Requested provider is not registered.");
        }

        return $this->providers[$identifier];
    }
}
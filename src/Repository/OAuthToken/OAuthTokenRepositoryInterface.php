<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 4/23/17
 * Time: 4:17 PM
 */

namespace TravelMap\Repository\OAuthToken;

use Gigablah\Silex\OAuth\Security\Authentication\Token\OAuthTokenInterface;
use TravelMap\Entity\OAuthToken;
use TravelMap\ValueObject\AccessToken;

interface OAuthTokenRepositoryInterface {

    /**
     * @param int $userId
     * @param OAuthTokenInterface $token
     * @return OAuthToken
     */
    public function createOAuthToken($userId, OAuthTokenInterface $token);

    /**
     * @param int $userId
     * @param OAuthToken $OAuthToken
     * @return OAuthToken
     */
    public function updateOAuthToken($userId, OAuthToken $OAuthToken);

    /**
     * @param int $userId
     * @param OAuthTokenInterface $token
     * @return null|OAuthToken
     */
    public function getOAuthToken($userId, OAuthTokenInterface $token);

    /**
     * @param int $userId
     * @return OAuthToken
     */
    public function getLastUsedOAuthToken($userId);

    /**
     * @param int $userId
     * @param string $service
     * @return null|AccessToken
     */
    public function getLastRefreshToken($userId, $service);
}
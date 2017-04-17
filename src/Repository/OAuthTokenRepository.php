<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/20/17
 * Time: 10:00 PM
 */

namespace TravelMap\Repository;

use Gigablah\Silex\OAuth\Security\Authentication\Token\OAuthTokenInterface;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use TravelMap\Entity\OAuthToken;
use TravelMap\ValueObject\AccessToken;
use TravelMap\ValueObject\DateTime;
use Doctrine\DBAL\Connection;
use TravelMap\ValueObject\Service;

final class OAuthTokenRepository {

    /** @var Connection */
    private $db;

    public function __construct(Connection $db) {
        $this->db = $db;
    }

    /**
     * @param int $userId
     * @param OAuthTokenInterface $token
     * @return OAuthToken
     */
    public function createOAuthToken($userId, OAuthTokenInterface $token) {
        $service = new Service($token->getService());
        $accessToken = new AccessToken($token->getCredentials());
        $refreshToken = null;
        if ($token->getAccessToken()->getRefreshToken() !== null) {
            $refreshToken = new AccessToken($token->getAccessToken()->getRefreshToken());
        }

        $created = new DateTime(date('Y-m-d H:i:s'));
        $date = date('Y-m-d H:i:s', $token->getAccessToken()->getEndOfLife());
        $expiresOn = new DateTime($date);
        $this->db->insert('oauth_token', [
            'user_id' => $userId,
            'service' => (string) $service,
            'access_token' => (string) $accessToken,
            'refresh_token' => $refreshToken,
            'expires_on' => $expiresOn,
            'created' => $created,
        ]);

        $id = $this->db->lastInsertId();

        return new OAuthToken(
            $id,
            $userId,
            $service,
            $accessToken,
            $expiresOn,
            $created,
            null,
            $refreshToken
        );
    }

    /**
     * @param int $userId
     * @param OAuthToken $OAuthToken
     * @return OAuthToken
     */
    public function updateOAuthToken($userId, OAuthToken $OAuthToken) {
        $service = $OAuthToken->getService();
        $accessToken = $OAuthToken->getAccessToken();
        $refreshToken = $OAuthToken->getRefreshToken();
        $lastTimeUsed = new DateTime(date('Y-m-d H:i:s'));
        $this->db->update('oauth_token', [
            'last_time_used' => $lastTimeUsed,
        ], [
            'service' => $service,
            'token' => $accessToken,
        ]);

        return new OAuthToken(
            $OAuthToken->getId(),
            $userId,
            $service,
            $accessToken,
            $OAuthToken->getExpiresOn(),
            $OAuthToken->getCreated(),
            $lastTimeUsed,
            $refreshToken
        );
    }

    /**
     * @param int $userId
     * @param OAuthTokenInterface $token
     * @return null|OAuthToken
     */
    public function getOAuthToken($userId, OAuthTokenInterface $token) {
        $query = <<<SQL
SELECT u.id, ot.service, ot.access_token, u.email, u.full_name, u.created, u.updated
FROM user AS u
INNER JOIN oauth_token AS ot ON ot.user_id = u.id
WHERE ot.access_token = ?
  AND ot.user_id = ?
SQL;
        $oathToken = $this->db->fetchAssoc($query, [ $token->getCredentials(), $userId ]);

        if (!$oathToken) {
            return null;
        }

        $expiresOnDate = date('Y-m-d H:i:s', $token->getAccessToken()->getEndOfLife());

        if ($token->getAccessToken()->getRefreshToken() !== null) {
            $refreshToken = new AccessToken($token->getAccessToken()->getRefreshToken());
        }
        else {
            $refreshToken = $this->getLastRefreshToken($userId, $oathToken['service']);
        }

        return new OAuthToken(
            $oathToken['id'],
            $userId,
            new Service($token->getService()),
            new AccessToken($token->getCredentials()),
            new DateTime($expiresOnDate),
            new DateTime($oathToken['created']),
            null,
            $refreshToken
        );
    }

    /**
     * @param int $userId
     * @return OAuthToken
     */
    public function getLastUsedOAuthToken($userId) {
        $query = <<<SQL
SELECT id, service, access_token, refresh_token, expires_on, created, last_time_used
FROM oauth_token
WHERE user_id = ?
ORDER BY last_time_used DESC 
LIMIT 1
SQL;
        $oathToken = $this->db->fetchAssoc($query, [ $userId ]);

        if (!$oathToken) {
            throw new AccessDeniedException("You need to log in to proceed.");
        }

        if ($oathToken['refresh_token'] !== null) {
            $refreshToken = new AccessToken($oathToken['refresh_token']);
        }
        else {
            $refreshToken = $this->getLastRefreshToken($userId, $oathToken['service']);
        }

        return new OAuthToken(
            $oathToken['id'],
            $userId,
            new Service($oathToken['service']),
            new AccessToken($oathToken['access_token']),
            new DateTime($oathToken['expires_on']),
            new DateTime($oathToken['created']),
            new DateTime($oathToken['last_time_used']),
            $refreshToken
        );
    }

    /**
     * @param int $userId
     * @param string $service
     * @throws AccessDeniedException
     * @return AccessToken
     */
    public function getLastRefreshToken($userId, $service) {
        $query = <<<SQL
SELECT refresh_token 
FROM oauth_token 
WHERE service = ? 
  AND user_id = ? 
  AND refresh_token IS NOT NULL 
ORDER BY expires_on DESC
SQL;
        $oathToken = $this->db->fetchAssoc($query, [ $service, $userId ]);

        if (!$oathToken) {
            throw new AccessDeniedException("You need to log in to proceed.");
        }

        return new AccessToken($oathToken['refresh_token']);
    }
}
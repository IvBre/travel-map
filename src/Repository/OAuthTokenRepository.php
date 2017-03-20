<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/20/17
 * Time: 10:00 PM
 */

namespace TravelMap\Repository;

use Gigablah\Silex\OAuth\Security\Authentication\Token\OAuthTokenInterface;
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
        $created = new DateTime(date('Y-m-d H:i:s'));
        $this->db->insert('oauth_token', [
            'user_id' => $userId,
            'service' => (string) $service,
            'access_token' => (string) $accessToken,
            'created' => $created,
        ]);

        $id = $this->db->lastInsertId();

        return new OAuthToken(
            $id,
            $userId,
            $service,
            $accessToken,
            $created
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

        return new OAuthToken(
            $oathToken['id'],
            $userId,
            new Service($token->getService()),
            new AccessToken($token->getCredentials()),
            new DateTime($oathToken['created'])
        );
    }
}
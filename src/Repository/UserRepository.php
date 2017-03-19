<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 6:26 PM
 */

namespace TravelMap\Repository;

use Doctrine\DBAL\Connection;
use TravelMap\Entity\User;
use TravelMap\ValueObject\AccessToken;
use TravelMap\ValueObject\DateTime;
use TravelMap\ValueObject\Email;
use TravelMap\ValueObject\Name;

final class UserRepository {

    /** @var Connection */
    private $db;

    public function __construct(Connection $db) {
        $this->db = $db;
    }

    /**
     * @param Email $email
     * @return null|User
     */
    public function getUserByEmail(Email $email) {
        //check if user exists
        $query = <<<SQL
SELECT id, access_token, email, first_name, last_name, created, updated
FROM user
WHERE email = ?
SQL;
        $user = $this->db->fetchAssoc($query, [ $email ]);

        if (!$user) {
            return null;
        }

        $accessToken = new AccessToken($user['access_token']);
        $email = new Email($user['email']);
        $firstName = new Name($user['first_name']);
        $lastName = new Name($user['last_name']);
        $created = new DateTime($user['created']);
        $updated = new DateTime($user['updated']);

        return new User(
            $user['id'],
            $accessToken,
            $email,
            $firstName,
            $lastName,
            $created,
            $updated
        );
    }

    /**
     * @param User $user
     * @return int
     */
    public function createUser(User $user) {
        return $this->db->insert('user', [
            'email' => $user->getEmail(),
            'access_token' => $user->getAccessToken(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
        ]);
    }

    /**
     * @param User $user
     * @return int
     */
    public function updateUser(User $user) {
        return $this->db->update('user', [
            'access_token' => $user->getAccessToken(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'updated' => (new \DateTime())->format('Y-m-d H:i:s')
        ], [
            'id' => $user->getId()
        ]);
    }
}
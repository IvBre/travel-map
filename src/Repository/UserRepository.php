<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 6:26 PM
 */

namespace TravelMap\Repository;

use Doctrine\DBAL\Connection;
use TravelMap\Entity\User;
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
SELECT id, full_name, created, updated
FROM user
WHERE email = ?
SQL;
        $user = $this->db->fetchAssoc($query, [ (string) $email ]);

        if (!$user) {
            return null;
        }

        $fullName = new Name($user['full_name']);
        $created = new DateTime($user['created']);
        $updated = new DateTime($user['updated']);

        return new User(
            $user['id'],
            $email,
            $fullName,
            $created,
            $updated
        );
    }

    public function getUserByShareToken($token) {
        $query = <<<SQL
SELECT id, email, full_name, created, updated
FROM user
WHERE share_token = ?
SQL;
        $user = $this->db->fetchAssoc($query, [ (string) $token ]);

        if (!$user) {
            return null;
        }

        $email = new Email($user['email']);
        $fullName = new Name($user['full_name']);
        $created = new DateTime($user['created']);
        $updated = new DateTime($user['updated']);

        return new User(
            $user['id'],
            $email,
            $fullName,
            $created,
            $updated
        );
    }

    public function getShareTokenByUserId($userId) {
        $query = <<<SQL
SELECT share_token
FROM user
WHERE id = ?
SQL;
        $result = $this->db->fetchAssoc($query, [ $userId ]);

        if ($result['share_token'] !== null) {
            return $result['share_token'];
        }

        $shareToken = md5(uniqid($userId, true));

        $dateTime = (new \DateTime())->format('Y-m-d H:i:s');
        $this->db->update('user', [
            'share_token' => $shareToken,
            'updated' => $dateTime
        ], [
            'id' => $userId
        ]);

        return $shareToken;
    }

    /**
     * @param Email $email
     * @param Name $fullName
     * @return User
     */
    public function createUser(Email $email, Name $fullName) {
        $created = new DateTime(date('Y-m-d H:i:s'));
        $this->db->insert('user', [
            'email' => $email,
            'full_name' => $fullName,
            'created' => $created,
        ]);
        $userId = $this->db->lastInsertId();

        return new User(
            $userId,
            $email,
            $fullName,
            $created
        );
    }

    /**
     * @param User $user
     * @param Name $fullName
     * @return User
     */
    public function updateUser(User $user, Name $fullName) {
        $dateTime = (new \DateTime())->format('Y-m-d H:i:s');
        $this->db->update('user', [
            'full_name' => $fullName,
            'updated' => $dateTime
        ], [
            'id' => $user->getId()
        ]);

        return new User(
            $user->getId(),
            $user->getEmail(),
            $fullName,
            $user->getCreated(),
            new DateTime($dateTime)
        );
    }
}
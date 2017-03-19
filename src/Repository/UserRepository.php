<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/19/17
 * Time: 6:26 PM
 */

namespace TravelMap\Repository;

use Doctrine\DBAL\Connection;
use TravelMap\Entity\User;

final class UserRepository {

    /** @var Connection */
    private $db;

    public function __construct(Connection $db) {
        $this->db = $db;
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
            'updated' => $user->getUpdated()
        ], [
            'id' => $user->getId()
        ]);
    }
}
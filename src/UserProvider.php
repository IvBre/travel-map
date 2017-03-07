<?php
/**
 * Created by PhpStorm.
 * User: ivana
 * Date: 1/25/17
 * Time: 9:55 PM
 */

namespace TravelMap;

use Doctrine\DBAL\Connection;
use TravelMap\Entity\User;

class UserProvider
{
    /** @var Connection */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function getUserByEmail($email) {
        //check if user exists
        $result = $this->db->createQueryBuilder()
            ->select('id, access_token, email, first_name, last_name')
            ->from('user')
            ->where('email = :email')
            ->setParameter('email', $email)
            ->execute()
            ->fetch();

        if (count($result) === 0) {
            return null;
        }

        $user = new User($result->id, $result->access_token, $result->emal, $result->first_name, $result->last_name);

        return $user;
    }

    public function createUser(User $user) {
        $this->db->insert('user', [
            'email' => $user->getEmail(),
            'access_token' => $user->getAccessToken(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'created' => date('Y-m-d H:m:s'),
        ]);
    }

    public function updateUser(User $user) {
        $this->db->update('user', [
            'access_token' => $user->getAccessToken(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
        ], [
            'id' => $user->getId()
        ]);
    }
}
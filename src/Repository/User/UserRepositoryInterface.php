<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 4/23/17
 * Time: 4:20 PM
 */

namespace TravelMap\Repository\User;

use TravelMap\Entity\User;
use TravelMap\ValueObject\Email;
use TravelMap\ValueObject\Name;

interface UserRepositoryInterface {

    /**
     * @param Email $email
     * @return null|User
     */
    public function getUserByEmail(Email $email);

    /**
     * @param string $token
     * @return null|User
     */
    public function getUserByShareToken($token);

    /**
     * @param int $userId
     * @return string
     */
    public function getShareTokenByUserId($userId);

    /**
     * @param Email $email
     * @param Name $fullName
     * @return User
     */
    public function createUser(Email $email, Name $fullName);

    /**
     * @param User $user
     * @param Name $fullName
     * @return User
     */
    public function updateUser(User $user, Name $fullName);
}
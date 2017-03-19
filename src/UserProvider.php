<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 1/25/17
 * Time: 9:55 PM
 */

namespace TravelMap;

use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use TravelMap\Entity\User;
use TravelMap\ValueObject\AccessToken;
use TravelMap\ValueObject\DateTime;
use TravelMap\ValueObject\Email;
use TravelMap\ValueObject\Name;

final class UserProvider implements UserProviderInterface {

    /** @var Connection */
    private $conn;

    public function __construct(Connection $conn) {
        $this->conn = $conn;
    }

    /** @inheritdoc */
    public function loadUserByUsername($username) {
        $stmt = $this->conn->executeQuery('SELECT * FROM user WHERE email = ?', array(strtolower($username)));
        if (!$user = $stmt->fetch()) {
            throw new UsernameNotFoundException(sprintf('Email "%s" does not exist.', $username));
        }

        $accessToken = new AccessToken($user['access_token']);
        $email = new Email($user['email']);
        $firstName = new Name($user['first_name']);
        $lastName = new Name($user['last_name']);
        $created = new DateTime($user['created']);
        $lastLogin = new DateTime($user['last_login']);

        return new User(
            $user['id'],
            $accessToken,
            $email,
            $firstName,
            $lastName,
            $created,
            $lastLogin
            );
    }

    /** @inheritdoc */
    public function refreshUser(UserInterface $user) {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /** @inheritdoc */
    public function supportsClass($class) {
        return $class === 'TravelMap\Entity\User';
    }
}
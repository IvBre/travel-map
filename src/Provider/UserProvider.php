<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 1/25/17
 * Time: 9:55 PM
 */

namespace TravelMap\Provider;

use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use TravelMap\Entity\User;
use TravelMap\Repository\UserRepository;
use TravelMap\ValueObject\AccessToken;
use TravelMap\ValueObject\DateTime;
use TravelMap\ValueObject\Email;
use TravelMap\ValueObject\Name;

final class UserProvider implements UserProviderInterface {

    /** @var UserRepository */
    private $repository;

    public function __construct(UserRepository $repository) {
        $this->repository = $repository;
    }

    /** @inheritdoc */
    public function loadUserByUsername($username) {
        $user = $this->repository->getUserByEmail(new Email($username));
        if ($user === null) {
            throw new UsernameNotFoundException(sprintf('Email "%s" does not exist.', $username));
        }

        return $user;
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
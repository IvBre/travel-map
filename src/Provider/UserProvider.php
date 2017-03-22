<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 1/25/17
 * Time: 9:55 PM
 */

namespace TravelMap\Provider;

use Gigablah\Silex\OAuth\Security\Authentication\Token\OAuthTokenInterface;
use Gigablah\Silex\OAuth\Security\User\Provider\OAuthUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use TravelMap\Entity\User;
use TravelMap\Repository\OAuthTokenRepository;
use TravelMap\Repository\UserRepository;
use TravelMap\ValueObject\Email;
use TravelMap\ValueObject\Name;

final class UserProvider implements UserProviderInterface, OAuthUserProviderInterface {

    /** @var OAuthTokenRepository */
    private $oAuthTokenRepository;

    /** @var UserRepository */
    private $repository;

    public function __construct(UserRepository $repository, OAuthTokenRepository $oAuthTokenRepository) {
        $this->repository = $repository;
        $this->oAuthTokenRepository = $oAuthTokenRepository;
    }

    /** @inheritdoc */
    public function loadUserByUsername($username) {
        $user = $this->repository->getUserByEmail($username);
        if ($user === null) {
            throw new UsernameNotFoundException(sprintf('Email "%s" does not exist.', $username));
        }

        $oauthToken = $this->oAuthTokenRepository->getLastUsedOAuthToken($user->getId());
        $user->setOAuth($oauthToken);

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

    /**
     * Loads a user based on OAuth credentials.
     *
     * @param OAuthTokenInterface $token
     *
     * @return UserInterface|null
     */
    public function loadUserByOAuthCredentials(OAuthTokenInterface $token) {
        $email = new Email($token->getEmail());
        $name = new Name($token->getUser());

        $user = $this->repository->getUserByEmail($email);

        // brand new user, we need to register it
        if ($user === null) {
            $user = $this->repository->createUser($email, $name);
        } else {
            $user = $this->repository->updateUser($user, $name);
        }

        $oauthToken = $this->oAuthTokenRepository->getOAuthToken($user->getId(), $token);
        if ($oauthToken === null) {
            $oauthToken = $this->oAuthTokenRepository->createOAuthToken($user->getId(), $token);
        } else {
            $oauthToken = $this->oAuthTokenRepository->updateOAuthToken($user->getId(), $oauthToken);
        }

        $user->setOAuth($oauthToken);

        return $user;
    }
}
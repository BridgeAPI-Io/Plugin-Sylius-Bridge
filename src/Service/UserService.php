<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Service;

use Sylius\Component\Core\Model\AdminUser;
use Sylius\Component\Core\Model\ShopUser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use function gettype;

final class UserService implements UserServiceInterface
{
    public function __construct(private TokenStorageInterface $tokenStorage)
    {
    }

    public function getAuthAdminUser(): AdminUser
    {
        $user = $this->getUser();

        if ($user === null) {
            throw new AccessDeniedException();
        }

        if (! $user instanceof AdminUser) {
            throw new AccessDeniedException();
        }

        return $user;
    }

    public function getAuthShopUser(): ShopUser
    {
        $user = $this->getUser();

        if ($user === null) {
            throw new AccessDeniedException();
        }

        if (! $user instanceof ShopUser) {
            throw new AccessDeniedException();
        }

        return $user;
    }

    public function getUser(): AdminUser|ShopUser|null
    {
        /** @var ?TokenInterface $token */
        $token = $this->tokenStorage->getToken();

        if ($token === null) {
            return null;
        }

        /** @var AdminUser|ShopUser|null $user */
        $user = $token->getUser();

        if (gettype($user) === 'string') {
            $user = null;
        }

        return $user;
    }
}

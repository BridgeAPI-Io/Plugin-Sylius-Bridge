<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Service;

use Sylius\Component\Core\Model\AdminUser;
use Sylius\Component\Core\Model\ShopUser;

interface UserServiceInterface
{
    public function getAuthAdminUser(): AdminUser;

    public function getAuthShopUser(): ShopUser;

    public function getUser(): AdminUser|ShopUser|null;
}

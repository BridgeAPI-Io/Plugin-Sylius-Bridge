<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Service;

interface BridgeBankServiceInterface
{
    /**
     * This function will sort the banks
     * The list will contain the locale banks then the rest of non locale banks
     */
    public function getSortedBanks(array $banks): array;

    /**
     * This function will filter the list of banks by search word
     */
    public function filterBanks(?array $banks, string $search): ?array;

    /**
     * This will sort the banks alphabetically
     */
    public function sortBanks(?array $banks): ?array;
}

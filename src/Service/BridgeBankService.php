<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Service;

use Sylius\Component\Locale\Context\LocaleContextInterface;

use function array_column;
use function array_merge;
use function array_multisort;
use function in_array;
use function str_starts_with;
use function strtolower;
use function strtoupper;
use function trim;

use const SORT_ASC;

final class BridgeBankService implements BridgeBankServiceInterface
{
    public const BANKS_TO_IGNORE = [159, 179, 5];

    public function __construct(
        private LocaleContextInterface $localeContext
    ) {
    }

    /**
     * This function allows to get only the locale banks depending on the locale code
     */
    private function getLocaleBanks(array $banks): array
    {
        $localBanks = [];

        foreach ($banks as $bank) {
            if (strtolower($bank['country_code']) !== strtolower($this->localeContext->getLocaleCode())) {
                continue;
            }

            $localBanks[] = $bank;
        }

        return $localBanks;
    }

    /**
     * This will sort the banks alphabetically
     */
    public function sortBanks(?array $banks): ?array
    {
        if ($banks === null) {
            return null;
        }

        $name = array_column($banks, 'name');

        array_multisort($name, SORT_ASC, $banks);

        return $banks;
    }

    /**
     * This function will sort the banks
     * The list will contain the locale banks then the rest of non locale banks
     */
    public function getSortedBanks(array $banks): array
    {
        $localeBanks = $this->getLocaleBanks($banks);

        $sortedLocaleBanks = $this->sortBanks($localeBanks);

        $otherBanks = [];

        foreach ($banks as $bank) {
            // Ignore LCL banks
            if (in_array($bank['id'], self::BANKS_TO_IGNORE, true)) {
                continue;
            }

            if (in_array($bank, $localeBanks, true)) {
                continue;
            }

            $otherBanks[] = $bank;
        }

        $sortedOtherBanks = $this->sortBanks($otherBanks);

        if ($sortedLocaleBanks === null) {
            $sortedLocaleBanks = [];
        }

        if ($sortedOtherBanks === null) {
            $sortedOtherBanks = [];
        }

        return array_merge($sortedLocaleBanks, $sortedOtherBanks);
    }

    public function filterBanks(?array $banks, string $search): ?array
    {
        if (trim($search) === '') {
            return $banks;
        }

        if ($banks === null) {
            return null;
        }

        $filteredList = [];

        $searchToUpper = strtoupper($search);

        foreach ($banks as $bank) {
            $bankName = strtoupper($bank['name']);

            // Ignore LCL banks
            if (in_array($bank['id'], self::BANKS_TO_IGNORE, true)) {
                continue;
            }

            if (! str_starts_with($bankName, $searchToUpper)) {
                continue;
            }

            $filteredList[] = $bank;
        }

        return $filteredList;
    }
}

<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Service;

use Sylius\Component\Locale\Context\LocaleContextInterface;

use function array_column;
use function array_filter;
use function array_multisort;
use function in_array;
use function str_starts_with;
use function strtolower;
use function strtoupper;
use function trim;

use const SORT_ASC;

final class BridgeBankService implements BridgeBankServiceInterface
{
    public const BANKS_TO_IGNORE = [152, 179, 5];

    public function __construct(
        private LocaleContextInterface $localeContext
    ) {
    }

    /**
     * This function allows to get only the locale banks depending on the locale code
     */
    private function getLocaleBanks(array $banks): array
    {
        $localeCode = strtolower($this->localeContext->getLocaleCode());

        return array_filter($banks, static function ($bank) use ($localeCode): bool {
            return strtolower($bank['country_code']) === strtolower($localeCode);
        });
    }

    /**
     * This will sort the banks alphabetically
     */
    public function sortBanks(array $banks): array
    {
        $name = array_column($banks, 'name');

        array_multisort($name, SORT_ASC, $banks);

        return $banks;
    }

    /**
     * This function will sort the banks
     * The list will contain only the locale banks
     */
    public function getSortedBanks(array $banks): array
    {
        $banks = $this->ignoreBanks($banks);

        $localeBanks = $this->getLocaleBanks($banks);

        return $this->sortBanks($localeBanks);
    }

    public function filterBanks(array $banks, string $search): array
    {
        $search = trim($search);

        if ($search === '') {
            return $banks;
        }

        $searchToUpper = strtoupper($search);

        return array_filter($banks, static function ($bank) use ($searchToUpper): bool {
            return str_starts_with(strtoupper($bank['name']), $searchToUpper);
        });
    }

    /**
     * This will filter out ignored banks
     */
    public function ignoreBanks(array $banks): array
    {
        return array_filter($banks, static function ($bank): bool {
            return ! in_array($bank['id'], self::BANKS_TO_IGNORE, true);
        });
    }
}

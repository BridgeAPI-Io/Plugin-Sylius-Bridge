<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Service;

use Sylius\Component\Locale\Context\LocaleContextInterface;

use function array_column;
use function array_filter;
use function array_multisort;
use function in_array;
use function Safe\substr;
use function str_starts_with;
use function strtolower;
use function strtoupper;
use function trim;

use const SORT_ASC;

final class BridgeBankService implements BridgeBankServiceInterface
{
    public const BANKS_TO_IGNORE = [152, 179, 5];

    /**
     * List of Supported banks from https://docs.bridgeapi.io/v2021.06.01/reference/list-banks
     */
    public const SUPPORTED_BANKS = ['fr', 'es', 'it', 'pt', 'de', 'be', 'nl', 'lu', 'pl', 'hu', 'ie', 'mt', 'cz', 'no', 'bg', 'se', 'dk', 'at', 'sk'];

    public function __construct(
        private LocaleContextInterface $localeContext,
    ) {
    }

    /**
     * This function allows to get only the locale banks depending on the locale code
     *
     * @param array<int, array{id:int,name:string,country_code:string,logo_url:string,url: string,is_highlighted:bool,primary_color:string,secondary_color:string,parent_name:string,capabilities:array<int, string>,channel_type: array<int, string>,display_order:int,authentication_page_url:string,authentication_page_url_mobile:string}> $banks
     *
     * @return array<int, array{id:int,name:string,country_code:string,logo_url:string,url: string,is_highlighted:bool,primary_color:string,secondary_color:string,parent_name:string,capabilities:array<int, string>,channel_type: array<int, string>,display_order:int,authentication_page_url:string,authentication_page_url_mobile:string}>
     */
    private function getLocaleBanks(array $banks): array
    {
        $localeCode = strtolower(substr($this->localeContext->getLocaleCode(), 0, 2));
        if (! in_array($localeCode, self::SUPPORTED_BANKS, true)) {
            $localeCode = 'fr';
        }

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

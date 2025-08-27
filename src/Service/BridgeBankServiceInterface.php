<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Service;

interface BridgeBankServiceInterface
{
    /**
     * This function will sort the banks
     * The list will contain the locale banks then the rest of non locale banks
     *
     * @param array<int, array{id:int,name:string,country_code:string,logo_url:string,url: string,is_highlighted:bool,primary_color:string,secondary_color:string,parent_name:string,capabilities:array<int, string>,channel_type: array<int, string>,display_order:int,authentication_page_url:string,authentication_page_url_mobile:string}> $banks
     *
     * @return array<int, array{id:int,name:string,country_code:string,logo_url:string,url: string,is_highlighted:bool,primary_color:string,secondary_color:string,parent_name:string,capabilities:array<int, string>,channel_type: array<int, string>,display_order:int,authentication_page_url:string,authentication_page_url_mobile:string}>
     */
    public function getSortedBanks(array $banks): array;

    /**
     * This function will filter the list of banks by search word
     *
     * @param array<int, array{id:int,name:string,country_code:string,logo_url:string,url: string,is_highlighted:bool,primary_color:string,secondary_color:string,parent_name:string,capabilities:array<int, string>,channel_type: array<int, string>,display_order:int,authentication_page_url:string,authentication_page_url_mobile:string}> $banks
     *
     * @return array<int, array{id:int,name:string,country_code:string,logo_url:string,url: string,is_highlighted:bool,primary_color:string,secondary_color:string,parent_name:string,capabilities:array<int, string>,channel_type: array<int, string>,display_order:int,authentication_page_url:string,authentication_page_url_mobile:string}>
     */
    public function filterBanks(array $banks, string $search): array;

    /**
     * This will sort the banks alphabetically
     *
     * @param array<int, array{id:int,name:string,country_code:string,logo_url:string,url: string,is_highlighted:bool,primary_color:string,secondary_color:string,parent_name:string,capabilities:array<int, string>,channel_type: array<int, string>,display_order:int,authentication_page_url:string,authentication_page_url_mobile:string}> $banks
     *
     * @return array<int, array{id:int,name:string,country_code:string,logo_url:string,url: string,is_highlighted:bool,primary_color:string,secondary_color:string,parent_name:string,capabilities:array<int, string>,channel_type: array<int, string>,display_order:int,authentication_page_url:string,authentication_page_url_mobile:string}>
     */
    public function sortBanks(array $banks): array;

    /**
     * This will filter out ignored banks
     *
     * @param array<int, array{id:int,name:string,country_code:string,logo_url:string,url: string,is_highlighted:bool,primary_color:string,secondary_color:string,parent_name:string,capabilities:array<int, string>,channel_type: array<int, string>,display_order:int,authentication_page_url:string,authentication_page_url_mobile:string}> $banks
     *
     * @return array<int, array{id:int,name:string,country_code:string,logo_url:string,url: string,is_highlighted:bool,primary_color:string,secondary_color:string,parent_name:string,capabilities:array<int, string>,channel_type: array<int, string>,display_order:int,authentication_page_url:string,authentication_page_url_mobile:string}>
     */
    public function ignoreBanks(array $banks): array;
}

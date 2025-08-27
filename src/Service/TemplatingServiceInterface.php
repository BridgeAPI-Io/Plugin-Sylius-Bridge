<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface TemplatingServiceInterface
{
    /** @param array<string, array<int, array{id:int,name:string,country_code:string,logo_url:string,url: string,is_highlighted:bool,primary_color:string,secondary_color:string,parent_name:string,capabilities:array<int, string>,channel_type: array<int, string>,display_order:int,authentication_page_url:string,authentication_page_url_mobile:string}>> $parameters */
    public function renderFromTemplate(string $view, array $parameters = [], ?Response $response = null): Response;

    /** @param array<string, array<int, array{id:int,name:string,country_code:string,logo_url:string,url: string,is_highlighted:bool,primary_color:string,secondary_color:string,parent_name:string,capabilities:array<int, string>,channel_type: array<int, string>,display_order:int,authentication_page_url:string,authentication_page_url_mobile:string}>> $parameters */
    public function render(Request $request, array $parameters = [], ?Response $response = null): Response;
}

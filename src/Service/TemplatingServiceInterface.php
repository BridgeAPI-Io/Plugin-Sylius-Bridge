<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface TemplatingServiceInterface
{
    public function renderFromTemplate(string $view, array $parameters = [], ?Response $response = null): Response;

    public function render(Request $request, array $parameters = [], ?Response $response = null): Response;
}

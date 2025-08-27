<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

final class TemplatingService implements TemplatingServiceInterface
{
    public function __construct(private EngineInterface|Environment $templatingEngine)
    {
    }

    private function getSyliusAttribute(Request $request, string $attribute, ?string $default = null): ?string
    {
        /** @var array<string, string>|null $attributes */
        $attributes = $request->attributes->get('_sylius');

        return $attributes[$attribute] ?? $default;
    }

    /**
     * Renders a view.
     *
     * @throws SyntaxError|RuntimeError|LoaderError
     */
    public function renderFromTemplate(string $view, array $parameters = [], ?Response $response = null): Response
    {
        $content = $this->templatingEngine->render($view, $parameters);

        if ($response === null) {
            $response = new Response();
        }

        $response->setContent($content);

        return $response;
    }

    /**
     * @param array<string, array<int, array{id:int,name:string,country_code:string,logo_url:string,url: string,is_highlighted:bool,primary_color:string,secondary_color:string,parent_name:string,capabilities:array<int, string>,channel_type: array<int, string>,display_order:int,authentication_page_url:string,authentication_page_url_mobile:string}>> $parameters
     *
     * @throws SyntaxError|RuntimeError|LoaderError
     */
    public function render(Request $request, array $parameters = [], ?Response $response = null): Response
    {
        $view = $this->getSyliusAttribute($request, 'template');
        if ($view === null) {
            return new Response();
        }

        return $this->renderFromTemplate($view, $parameters, $response);
    }
}

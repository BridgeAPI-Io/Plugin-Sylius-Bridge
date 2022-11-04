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

    private function getSyliusAttribute(Request $request, string $attribute, ?string $default = null): string
    {
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
     * @throws SyntaxError|RuntimeError|LoaderError
     */
    public function render(Request $request, array $parameters = [], ?Response $response = null): Response
    {
        $view = $this->getSyliusAttribute($request, 'template');

        return $this->renderFromTemplate($view, $parameters, $response);
    }
}

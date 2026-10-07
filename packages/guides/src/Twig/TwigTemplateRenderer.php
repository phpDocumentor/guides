<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Guides\Twig;

use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\TemplateRenderer;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\TemplateWrapper;

final class TwigTemplateRenderer implements TemplateRenderer
{
    private Environment|null $environment = null;

    private RenderContext|null $globalsContext = null;

    /** @var array<string, TemplateWrapper> */
    private array $templates = [];

    public function __construct(private readonly EnvironmentBuilder $environmentBuilder)
    {
    }

    /** @param array<string, mixed> $params */
    public function renderTemplate(RenderContext $context, string $template, array $params = []): string
    {
        $twig = $this->environmentBuilder->getTwigEnvironment();
        if ($this->environment !== $twig) {
            $this->environment = $twig;
            $this->globalsContext = null;
            $this->templates = [];
        }

        // the "env" check catches contexts set elsewhere, e.g. by EnvironmentBuilder::setContext()
        if ($this->globalsContext !== $context || ($twig->getGlobals()['env'] ?? null) !== $context) {
            $twig->addGlobal('env', $context);
            $twig->addGlobal('debugInformation', $context->getLoggerInformation());
            $this->globalsContext = $context;
        }

        return ($this->templates[$template] ??= $twig->load($template))->render($params);
    }

    public function isTemplateFound(RenderContext $context, string $template): bool
    {
        try {
            $twig = $this->environmentBuilder->getTwigEnvironment();
            $twig->load($template);

            return true;
        } catch (LoaderError) {
        }

        return false;
    }
}

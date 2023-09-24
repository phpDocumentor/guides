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
use Twig\Error\LoaderError;

use function sprintf;
use function strpos;

final class TwigTemplateRenderer implements TemplateRenderer
{
    public function __construct(private readonly EnvironmentBuilder $environmentBuilder)
    {
    }

    /** @param array<string, mixed> $params */
    public function renderTemplate(RenderContext $context, string $template, array $params = []): string
    {
        $twig = $this->environmentBuilder->getTwigEnvironment();
        $twig->addGlobal('env', $context);

        if (strpos($template, '%s') !== false) {
            $template = sprintf($template, $context->getOutputFormat());
        }

        return $twig->render($template, $params);
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

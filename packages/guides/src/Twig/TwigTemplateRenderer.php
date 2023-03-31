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

final class TwigTemplateRenderer implements TemplateRenderer
{
    private EnvironmentBuilder $environmentBuilder;

    public function __construct(EnvironmentBuilder $environmentBuilder)
    {
        $this->environmentBuilder = $environmentBuilder;
    }

    /**
     * @param array<string, mixed> $params
     */
    public function renderTemplate(RenderContext $context, string $template, array $params = []): string
    {
        $twig = $this->environmentBuilder->getTwigEnvironment();
        $twig->addGlobal('env', $context);

        return $twig->render($template, $params);
    }
}

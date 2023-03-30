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

use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\NodeRenderers\NodeRendererFactory;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\TemplateRenderer;

final class TwigTemplateRenderer implements TemplateRenderer, NodeRenderer
{
    private EnvironmentBuilder $environmentBuilder;
    private NodeRendererFactory $nodeRendererFactory;

    public function __construct(EnvironmentBuilder $environmentBuilder, NodeRendererFactory $nodeRendererFactory)
    {
        $this->environmentBuilder = $environmentBuilder;
        $this->nodeRendererFactory = $nodeRendererFactory;
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

    public function supports(Node $node): bool
    {
        return true;
    }

    public function render(Node $node, RenderContext $renderContext): string
    {
        return $this->nodeRendererFactory->get($node)->render($node, $renderContext);
    }
}

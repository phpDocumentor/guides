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

namespace phpDocumentor\Guides\NodeRenderers\Html;

use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\Menu\MenuEntryNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\UrlGenerator\UrlGeneratorInterface;
use phpDocumentor\Guides\TemplateRenderer;

use function is_a;

/** @implements NodeRenderer<MenuEntryNode> */
final class MenuEntryRenderer implements NodeRenderer
{
    public function __construct(
        private readonly TemplateRenderer $renderer,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function supports(string $nodeFqcn): bool
    {
        return $nodeFqcn === MenuEntryNode::class || is_a($nodeFqcn, MenuEntryNode::class, true);
    }

    public function render(Node $node, RenderContext $renderContext): string
    {
        $url = $this->urlGenerator->generateCanonicalOutputUrl($renderContext, $node->getUrl(), $node->getValue()?->getId());

        return $this->renderer->renderTemplate(
            $renderContext,
            'body/menu/menu-item.html.twig',
            [
                'url' => $url,
                'node' => $node,
            ],
        );
    }
}

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

namespace phpDocumentor\Guides\Pages\NodeRenderers\Html;

use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Pages\Nodes\ContentTypeItemNode;
use phpDocumentor\Guides\Pages\Nodes\ContentTypeOverviewNode;
use phpDocumentor\Guides\Pages\Nodes\RenderablePageInterface;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\TemplateRenderer;

use function assert;
use function is_a;

/**
 * Renders any {@see RenderablePageInterface} node — static pages, content-type
 * items, and collection overview pages — for the "page" output format.
 *
 * Template resolution is fully node-driven:
 *
 * - {@see ContentTypeOverviewNode}: uses {@see ContentTypeOverviewNode::getTemplate()},
 *   which holds the `overview_template` value from `guides.xml`.
 * - {@see ContentTypeItemNode}: uses {@see ContentTypeItemNode::getItemTemplate()},
 *   which is either the per-item `:page-template:` RST field or the
 *   collection-level `item_template` stamped on by
 *   {@see \phpDocumentor\Guides\Pages\EventListener\ParseContentTypeListener}.
 *   Falls back to `structure/content-type-item.html.twig`.
 * - All other {@see RenderablePageInterface} nodes (including {@see PageNode}):
 *   `structure/page.html.twig`.
 *
 * @implements NodeRenderer<RenderablePageInterface>
 */
final class PageNodeRenderer implements NodeRenderer
{
    private const DEFAULT_PAGE_TEMPLATE     = 'structure/page.html.twig';
    private const DEFAULT_ITEM_TEMPLATE     = 'structure/content-type-item.html.twig';

    public function __construct(
        private readonly TemplateRenderer $renderer,
    ) {
    }

    public function supports(string $nodeFqcn): bool
    {
        return is_a($nodeFqcn, RenderablePageInterface::class, true);
    }

    public function render(Node $node, RenderContext $renderContext): string
    {
        assert($node instanceof RenderablePageInterface);

        $template = $this->resolveTemplate($node);
        $params   = [
            'node'  => $node,
            'title' => $node->getPageTitle(),
        ];

        if ($node instanceof ContentTypeOverviewNode) {
            $params['items'] = $node->getItems();
        }

        return $this->renderer->renderTemplate($renderContext, $template, $params);
    }

    private function resolveTemplate(RenderablePageInterface $node): string
    {
        if ($node instanceof ContentTypeOverviewNode) {
            return $node->getTemplate();
        }

        if ($node instanceof ContentTypeItemNode) {
            return $node->getItemTemplate() ?? self::DEFAULT_ITEM_TEMPLATE;
        }

        return self::DEFAULT_PAGE_TEMPLATE;
    }
}

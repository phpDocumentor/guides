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
use phpDocumentor\Guides\Pages\Nodes\PageNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\TemplateRenderer;

use function assert;
use function is_a;

/**
 * Renders a {@see PageNode} as a self-contained HTML page for the "page" output format.
 *
 * The template used is `structure/page.html.twig`, provided by this package under
 * `resources/template/page/`. The template receives:
 *
 * - `node`  — the {@see PageNode} being rendered
 * - `title` — the page title (may be null)
 *
 * @template T as Node
 * @implements NodeRenderer<PageNode>
 */
final class PageNodeRenderer implements NodeRenderer
{
    private string $template = 'structure/page.html.twig';

    public function __construct(
        private readonly TemplateRenderer $renderer,
    ) {
    }

    public function supports(string $nodeFqcn): bool
    {
        return $nodeFqcn === PageNode::class || is_a($nodeFqcn, PageNode::class, true);
    }

    /** @param T $node */
    public function render(Node $node, RenderContext $renderContext): string
    {
        assert($node instanceof PageNode);

        return $this->renderer->renderTemplate(
            $renderContext,
            $this->template,
            [
                'node'  => $node,
                'title' => $node->getPageTitle(),
            ],
        );
    }
}

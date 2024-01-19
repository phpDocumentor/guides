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
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\TemplateRenderer;

use function assert;
use function is_a;

/**
 * @template T as Node
 * @implements NodeRenderer<DocumentNode>
 */
final class DocumentNodeRenderer implements NodeRenderer
{
    private string $template = 'structure/document.html.twig';

    public function __construct(
        private readonly TemplateRenderer $renderer,
    ) {
    }

    public function supports(string $nodeFqcn): bool
    {
        return $nodeFqcn === DocumentNode::class || is_a($nodeFqcn, DocumentNode::class, true);
    }

    /** @param T $node */
    public function render(Node $node, RenderContext $renderContext): string
    {
        assert($node instanceof DocumentNode);
        $data = [
            'node' => $node,
            'title' => $node->getPageTitle(),
            'parts' => $node->getDocumentPartNodes(),
        ];

        return $this->renderer->renderTemplate(
            $renderContext,
            $this->template,
            $data,
        );
    }
}

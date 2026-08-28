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
use phpDocumentor\Guides\Nodes\Metadata\TemplateNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;

use function is_a;

/**
 * The `:template:` field is a marker consumed by DocumentNode::getTemplate(),
 * not visible content — render it as nothing.
 *
 * @implements NodeRenderer<TemplateNode>
 */
final class TemplateMetadataNodeRenderer implements NodeRenderer
{
    public function render(Node $node, RenderContext $renderContext): string
    {
        return '';
    }

    public function supports(string $nodeFqcn): bool
    {
        return $nodeFqcn === TemplateNode::class || is_a($nodeFqcn, TemplateNode::class, true);
    }
}

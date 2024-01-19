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

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\Nodes\ReplacementNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

use function count;

/**
 * The Replace directive will set the variables for the spans
 *
 * .. |test| replace:: The Test String!
 */
final class ReplaceDirective extends SubDirective
{
    public function getName(): string
    {
        return 'replace';
    }

    /** {@inheritDoc}
     *
     * @param Directive $directive
     */
    final protected function processSub(
        BlockContext $blockContext,
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        /** @var array<InlineCompoundNode> $children */
        $children = $collectionNode->getChildren();
        $data = $directive->getDataNode();
        if ($data !== null) {
            if (count($children) > 0) {
                $children[] = new ParagraphNode([$data]);
            } else {
                $children[] = $data;
            }
        }

        return new ReplacementNode(
            $children,
        );
    }
}

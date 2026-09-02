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
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\QuoteNode;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

/**
 * Highlights summarize the main points of a document or section, often consisting of a list.
 * The "highlights" directive produces a "highlights"-class block quote.
 *
 * https://docutils.sourceforge.io/docs/ref/rst/directives.html#highlights
 */
#[Attributes\Directive(name: 'highlights')]
final class HighlightsDirective extends SubDirective
{
    /** {@inheritDoc}
     *
     * @param Directive $directive
     */
    protected function processSub(
        BlockContext $blockContext,
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node {
        return $this->createNode(
            new DirectiveNode(
                $directive,
                $collectionNode->getChildren(),
            ),
        );
    }

    public function createNode(DirectiveNode $directiveNode): Node
    {
        return new QuoteNode($directiveNode->getChildren(), ['highlights']);
    }
}

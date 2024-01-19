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
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

/**
 * A pull-quote is a small selection of text "pulled out and quoted", typically in
 * a larger typeface. Pull-quotes are used to attract attention, especially in long articles.
 * The "pull-quote" directive produces a "pull-quote"-class block quote.
 *
 * https://docutils.sourceforge.io/docs/ref/rst/directives.html#pull-quote
 */
final class PullQuoteDirective extends SubDirective
{
    public function getName(): string
    {
        return 'pull-quote';
    }

    /** {@inheritDoc}
     *
     * @param Directive $directive
     */
    protected function processSub(
        BlockContext $blockContext,
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        return new QuoteNode($collectionNode->getChildren(), ['pull-quote']);
    }
}

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

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;

/** @implements Rule<CollectionNode> */
final class DirectiveContentRule implements Rule
{
    public function __construct(private readonly RuleContainer $bodyElements)
    {
    }

    public function applies(BlockContext $blockContext): bool
    {
        return true;
    }

    public function apply(BlockContext $blockContext, CompoundNode|null $on = null): Node|null
    {
        $node = new CollectionNode([]);
        $documentIterator = $blockContext->getDocumentIterator();
        // We explicitly do not use foreach, but rather the cursors of the DocumentIterator
        // this is done because we are transitioning to a method where a Substate can take the current
        // cursor as starting point and loop through the cursor
        while ($documentIterator->valid()) {
            $this->bodyElements->apply($blockContext, $node);
        }
        
        return $node;
    }
}

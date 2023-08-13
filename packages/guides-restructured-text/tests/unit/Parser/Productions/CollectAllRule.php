<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\RawNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Buffer;

/** @implements Rule<RawNode> */
class CollectAllRule implements Rule
{
    public function applies(BlockContext $blockContext): bool
    {
        return true;
    }

    public function apply(BlockContext $blockContext, CompoundNode|null $on = null): Node|null
    {
        $buffer = new Buffer();
        while ($blockContext->getDocumentIterator()->valid()) {
            $buffer->push($blockContext->getDocumentIterator()->current());
            $blockContext->getDocumentIterator()->next();
        }

        return new RawNode($buffer->getLinesString());
    }
}

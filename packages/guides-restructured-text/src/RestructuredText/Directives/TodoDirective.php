<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Nodes\CollectionNode;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

/**
 * Todo directives are treated as comments, omitting all content or options
 */
class TodoDirective extends SubDirective
{
    public function getName(): string
    {
        return 'todo';
    }

    protected function processSub(
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        return null;
    }
}

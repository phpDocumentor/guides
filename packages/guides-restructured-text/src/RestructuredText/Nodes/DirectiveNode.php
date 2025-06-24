<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Nodes;

use phpDocumentor\Guides\Nodes\AbstractNode;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

/** @extends AbstractNode<Directive> */
final class DirectiveNode extends AbstractNode
{
    public function __construct(private Directive $directive)
    {

    }

    public function getDirective(): Directive
    {
        return $this->directive;
    }
}

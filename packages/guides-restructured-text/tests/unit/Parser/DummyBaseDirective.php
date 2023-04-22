<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Directives\BaseDirective as DirectiveHandler;

class DummyBaseDirective extends DirectiveHandler
{
    private string $name = 'dummy';

    public function getName(): string
    {
        return $this->name;
    }

    public function process(
        DocumentParserContext $documentParserContext,
        Directive $directive,
    ): Node|null {
        return new DummyNode($directive->getVariable(), $directive->getData(), $directive->getOptions());
    }
}

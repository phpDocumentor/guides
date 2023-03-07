<?php

namespace phpDocumentor\Guides\RestructuredText\Parser;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Directives\Directive as DirectiveHandler;

class DummyDirective extends DirectiveHandler
{
    private string $name = 'dummy';

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param DirectiveOption[] $options the array of options for this directive
     */
    public function process(
        DocumentParserContext $documentParserContext,
        string                $variable,
        string                $data,
        array                 $options
    ): ?Node {
        return new DummyNode($variable, $data, $options);
    }
}

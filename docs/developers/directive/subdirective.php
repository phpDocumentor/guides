<?php

namespace YourExtension\Directives;

use phpDocumentor\Guides\RestructuredText\Directives\Attributes\Directive;
use phpDocumentor\Guides\RestructuredText\Directives\Attributes\Option;
use phpDocumentor\Guides\RestructuredText\Directives\OptionType;
use phpDocumentor\Guides\RestructuredText\Directives\SubDirective;
use phpDocumentor\Guides\Nodes\Node;

#[Directive(name: 'example')]
#[Option(name: 'option1', type: OptionType::Boolean, description: 'An example option', default: false)]
class ExampleSubDirective extends SubDirective
{
    public function createNode(\phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode $directiveNode): Node
    {
        return new ExampleNode(
            $this->readOption($directiveNode, 'option1'),
            $directiveNode->getDataNode(),
            $this->text,
            $directiveNode->getChildren(),
        );
    }
}

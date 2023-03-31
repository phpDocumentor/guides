<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes;

/**
 * This node is added by the \phpDocumentor\Guides\RestructuredText\Directives\ClassDirective if there are no
 * sub nodes within the class directive. In this case the class is added to node in the AST following
 * the class directive.
 *
 * The class node itself is not rendered.
 */

/** @extends AbstractNode<string> */
class ClassNode extends AbstractNode
{
    public function __construct(string $value)
    {
        $this->value = $value;
    }
}

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

namespace phpDocumentor\Guides\Nodes;

/**
 * This node is added by the \phpDocumentor\Guides\RestructuredText\Directives\ClassDirective if there are no
 * sub nodes within the class directive. In this case the class is added to node in the AST following
 * the class directive.
 *
 * The class node itself is not rendered.
 */

/** @extends AbstractNode<string> */
final class ClassNode extends AbstractNode
{
    public function __construct(string $value)
    {
        $this->value = $value;
    }
}

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
 * Any Directive not explicitly modeled.
 *
 * To support custom directives, this node type will capture any Directive declaration not otherwise defined
 * in a directive's node definition.
 *
 * A generic node contains the name of the Directive (`.. name::`) and it can have options (`:option:`); in addition
 * a value can be set after the directive invocation that can be read using the `getValue()` method.
 *
 * Example:
 *
 *     .. name:: value of this directive
 *        :option: optionValue
 *
 * @extends AbstractNode<Node|string|null>
 */
final class GenericNode extends AbstractNode
{
    public function __construct(private readonly string $name, Node|string|null $value = null)
    {
        $this->value = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }
}

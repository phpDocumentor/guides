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
 * Defines a footnote or citation
 *
 * @extends CompoundNode<InlineCompoundNode>
 */
abstract class AnnotationNode extends CompoundNode
{
    /** @param list<InlineCompoundNode> $value */
    public function __construct(array $value, private readonly string $name)
    {
        parent::__construct($value);
    }

    public function getName(): string
    {
        return $this->name;
    }

    abstract public function getAnchor(): string;
}

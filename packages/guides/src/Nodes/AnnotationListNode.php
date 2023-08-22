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
 * Defines a list of footnotes or citations
 *
 * @extends CompoundNode<AnnotationNode>
 */
final class AnnotationListNode extends CompoundNode
{
    /** @param list<AnnotationNode> $value */
    public function __construct(array $value, private readonly string $name)
    {
        parent::__construct($value);
    }

    public function getName(): string
    {
        return $this->name;
    }
}

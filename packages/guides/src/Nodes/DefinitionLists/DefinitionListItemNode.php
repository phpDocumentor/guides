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

namespace phpDocumentor\Guides\Nodes\DefinitionLists;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\SpanNode;
use RuntimeException;

final class DefinitionListItemNode extends Node
{
    private SpanNode $term;

    /** @var SpanNode[] */
    private array $classifiers;

    /**
     * @param SpanNode[] $classifiers
     * @param DefinitionNode[] $definitions
     */
    public function __construct(SpanNode $term, array $classifiers, array $definitions = [])
    {
        $this->term = $term;
        $this->classifiers = $classifiers;
        parent::__construct($definitions);
    }

    public function getTerm(): SpanNode
    {
        return $this->term;
    }

    /**
     * @return SpanNode[]
     */
    public function getClassifiers(): array
    {
        return $this->classifiers;
    }
}

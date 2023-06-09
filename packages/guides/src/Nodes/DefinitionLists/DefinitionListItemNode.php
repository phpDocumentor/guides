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

use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;

/** @extends CompoundNode<DefinitionNode> */
final class DefinitionListItemNode extends CompoundNode
{
    /**
     * @param InlineCompoundNode[] $classifiers
     * @param DefinitionNode[] $definitions
     */
    public function __construct(private readonly InlineCompoundNode $term, private readonly array $classifiers, array $definitions = [])
    {
        parent::__construct($definitions);
    }

    public function getTerm(): InlineCompoundNode
    {
        return $this->term;
    }

    /** @return InlineCompoundNode[] */
    public function getClassifiers(): array
    {
        return $this->classifiers;
    }
}

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

use phpDocumentor\Guides\Nodes\DefinitionLists\DefinitionListItemNode;

use function array_values;

/** @extends CompoundNode<DefinitionListItemNode> */
final class DefinitionListNode extends CompoundNode
{
    //phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
    public function __construct(DefinitionListItemNode ...$definitionListItems)
    {
        parent::__construct(array_values($definitionListItems));
    }
}

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

namespace phpDocumentor\Guides\Nodes\DocumentTree;

use phpDocumentor\Guides\Nodes\AbstractNode;

/**
 * @template TValue
 * @extends AbstractNode<TValue>
 */
abstract class EntryNode extends AbstractNode
{
    private DocumentEntryNode|null $parent = null;

    public function getParent(): DocumentEntryNode|null
    {
        return $this->parent;
    }

    public function setParent(DocumentEntryNode|null $parent): void
    {
        $this->parent = $parent;
    }
}

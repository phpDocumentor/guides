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

namespace phpDocumentor\Guides\Nodes\FieldLists;

use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\Node;

/** @extends CompoundNode<Node> */
final class FieldListItemNode extends CompoundNode
{
    /** @param Node[] $children */
    public function __construct(private readonly string $term, private string $plaintextContent = '', array $children = [])
    {
        parent::__construct($children);
    }

    public function getTerm(): string
    {
        return $this->term;
    }

    public function getPlaintextContent(): string
    {
        return $this->plaintextContent;
    }

    public function setPlaintextContent(string $plaintextContent): void
    {
        $this->plaintextContent = $plaintextContent;
    }

    public function addPlaintextContentLine(string $plaintextContent): void
    {
        if ($this->plaintextContent !== '') {
            $this->plaintextContent .= "\n";
        }

        $this->plaintextContent .= $plaintextContent;
    }
}

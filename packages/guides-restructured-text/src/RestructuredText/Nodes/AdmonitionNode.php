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

namespace phpDocumentor\Guides\RestructuredText\Nodes;

use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\Node;

/** @extends CompoundNode<Node> */
class AdmonitionNode extends CompoundNode
{
    private string $name;

    private string $text;

    /** {@inheritDoc} */
    public function __construct(string $name, string $text, array $value)
    {
        parent::__construct($value);

        $this->name = $name;
        $this->text = $text;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getText(): string
    {
        return $this->text;
    }
}

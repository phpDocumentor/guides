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
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Node;

/**
 * A catch-all directive Node containing all information about the original directive in rst.
 *
 * @extends CompoundNode<Node>
 */
class GeneralDirectiveNode extends CompoundNode
{
    /** @param list<Node> $value */
    public function __construct(
        private readonly string $name,
        private readonly string $plainContent,
        private readonly InlineCompoundNode $content,
        array $value = [],
    ) {
        parent::__construct($value);
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getPlainContent(): string
    {
        return $this->plainContent;
    }

    public function getContent(): InlineCompoundNode
    {
        return $this->content;
    }
}

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

use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\Node;

use function array_values;

final class IncludeDirectiveData implements DirectiveNodeChildrenData
{
    public function __construct(
        public readonly CollectionNode $content,
        public readonly string $sourcePath,
    ) {
    }

    /** @return list<Node> */
    public function getChildren(): array
    {
        return array_values($this->content->getChildren());
    }
}

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

namespace phpDocumentor\Guides\Nodes\Metadata;

use phpDocumentor\Guides\Nodes\Node;

/**
 * The author element holds the name of the author of the document.
 */
final class AuthorNode extends MetadataNode
{
    /** @param Node[] $children */
    public function __construct(string $plaintext, private readonly array $children)
    {
        parent::__construct($plaintext);
    }

    /** @return Node[] */
    public function getChildren(): array
    {
        return $this->children;
    }
}

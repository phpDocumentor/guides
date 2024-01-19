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
 * The organization element contains the name of document author's organization,
 * or the organization responsible for the document.
 */
final class OrganizationNode extends MetadataNode
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

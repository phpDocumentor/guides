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

/**
 * The authors element is a container for author information for documents with multiple authors.
 */
final class AuthorsNode extends MetadataNode
{
    /** @param AuthorNode[] $authorNodes */
    public function __construct(private readonly array $authorNodes)
    {
        parent::__construct('');
    }

    /** @return AuthorNode[] */
    public function getAuthorNodes(): array
    {
        return $this->authorNodes;
    }
}

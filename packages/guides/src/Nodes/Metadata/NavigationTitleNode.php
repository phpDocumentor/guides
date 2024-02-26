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
 * The organization element contains the name of document author's organization,
 * or the organization responsible for the document.
 */
final class NavigationTitleNode extends MetadataNode
{
    public function __construct(string $plaintext)
    {
        parent::__construct($plaintext);
    }
}

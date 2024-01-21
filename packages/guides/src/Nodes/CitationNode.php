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

use function strtolower;

/**
 * Defines a citation that can be referenced by an CitationInlineNode
 *
 * Lorem ipsum [Ref]_ dolor sit amet.
 *
 * .. [Ref] Book or article reference, URL or whatever.
 */
final class CitationNode extends AnnotationNode
{
    public function getAnchor(): string
    {
        return 'citation-' . strtolower($this->getName());
    }
}

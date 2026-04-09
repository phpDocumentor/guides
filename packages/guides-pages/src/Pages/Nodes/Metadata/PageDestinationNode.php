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

namespace phpDocumentor\Guides\Pages\Nodes\Metadata;

use phpDocumentor\Guides\Nodes\Metadata\MetadataNode;

/**
 * Declares the output path for a standalone page.
 *
 * Format-specific parsers (RST directives, Markdown front-matter handlers, etc.)
 * should emit this node into the parsed document's header nodes whenever they
 * encounter the relevant page-destination declaration. The
 * {@see \phpDocumentor\Guides\Pages\EventListener\RunPagesListener} reads this
 * node to determine where the finished HTML file should be written.
 *
 * The value stored is the output path **relative to the output root**, without
 * a file extension (e.g. `"about/company"`).
 *
 * @extends MetadataNode<string>
 */
final class PageDestinationNode extends MetadataNode
{
    public function __construct(string $destination)
    {
        parent::__construct($destination);
    }

    /**
     * Returns the output path relative to the output root, without extension.
     */
    public function getDestination(): string
    {
        return $this->value ?? '';
    }
}

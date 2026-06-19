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
 * Carries a per-item Twig template path override declared in a content-type
 * item source file via the `:page-template:` RST field-list entry.
 *
 * When present, the path stored here takes precedence over the collection-level
 * `item-template` configured in `guides.xml`. The path is relative to the
 * template directories registered by the extension (e.g.
 * `"structure/my-custom-item.html.twig"`).
 */
final class ContentTypeTemplateNode extends MetadataNode
{
    public function __construct(string $templatePath)
    {
        parent::__construct($templatePath);
    }

    /**
     * Returns the Twig template path relative to the registered template directories.
     */
    public function getTemplatePath(): string
    {
        return $this->getValue() ?? '';
    }
}

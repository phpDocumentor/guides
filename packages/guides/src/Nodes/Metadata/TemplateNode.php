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
 * Selects an alternate template for rendering the document, e.g. `:template: genindex`.
 */
final class TemplateNode extends MetadataNode
{
    public function __construct(string $template)
    {
        parent::__construct($template);
    }
}

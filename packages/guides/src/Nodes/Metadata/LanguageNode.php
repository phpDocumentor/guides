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
 * Sets the language of the document, e.g. `:lang: ar`, used as the HTML lang attribute.
 */
final class LanguageNode extends MetadataNode
{
    public function __construct(string $language)
    {
        parent::__construct($language);
    }
}

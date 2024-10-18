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

namespace phpDocumentor\Guides\Markdown\Parsers\FrontMatter;

use phpDocumentor\Guides\Nodes\DocumentNode;

final class TitleParser implements Parser
{
    /** {@inheritDoc} */
    public function process(DocumentNode $document, mixed $value, array $frontMatter): void
    {
        $document->setMetaTitle('' . $value);
    }

    public function field(): string
    {
        return 'title';
    }
}

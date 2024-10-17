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
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\Metadata\AuthorNode;

final class AuthorParser implements Parser
{
    /** {@inheritDoc} */
    public function process(DocumentNode $document, mixed $value, array $frontMatter): void
    {
        $value = '' . $value;
        $document->addHeaderNode(new AuthorNode($value, [new PlainTextInlineNode($value)]));
    }

    public function field(): string
    {
        return 'title';
    }
}

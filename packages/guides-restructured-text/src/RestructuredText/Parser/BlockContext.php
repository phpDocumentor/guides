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

namespace phpDocumentor\Guides\RestructuredText\Parser;

/**
 * Our document parser contains
 */
class BlockContext
{
    private LinesIterator $documentIterator;
    
    public function __construct(
        private readonly DocumentParserContext $documentParserContext,
        string $contents,
        bool $preserveSpace = false,
    ) {
        $this->documentIterator = new LinesIterator();
        $this->documentIterator->load($contents, $preserveSpace);
    }
    
    public function getDocumentIterator(): LinesIterator
    {
        return $this->documentIterator;
    }

    public function getDocumentParserContext(): DocumentParserContext
    {
        return $this->documentParserContext;
    }
}

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

use function array_merge;

/**
 * Our document parser contains
 */
final class BlockContext
{
    private readonly LinesIterator $documentIterator;
    
    public function __construct(
        private readonly DocumentParserContext $documentParserContext,
        string $contents,
        bool $preserveSpace = false,
        private readonly int $lineOffset = 0,
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

    /** @return array<string, int|string> */
    public function getLoggerInformation(): array
    {
        $info = [
            'currentLineNumber' => $this->lineOffset + 1,
        ];
        if ($this->documentIterator->valid()) {
            $info = array_merge($info, [
                'currentLine' => $this->documentIterator->current(),
                'currentLineNumber' => $this->lineOffset + $this->documentIterator->key(),
            ]);
        }

        return [...$this->documentParserContext->getLoggerInformation(), ...$info];
    }
}

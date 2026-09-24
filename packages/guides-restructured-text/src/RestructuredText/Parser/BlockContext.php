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

use function count;
use function max;
use function min;

/**
 * Our document parser contains
 */
final class BlockContext
{
    private readonly LinesIterator $documentIterator;
    
    /**
     * @param int $lineOffset Number of lines in the source file before the first line of $contents,
     *                        {@see getLineOffset()} of the parent context.
     */
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

    /**
     * Number of lines in the source file before the line at $key of this
     * context, to be passed on as lineOffset of a sub context whose contents
     * start at that line.
     */
    public function getLineOffset(int $key): int
    {
        return $this->lineOffset + $this->documentIterator->getLeadingLinesRemoved() + $key;
    }

    /**
     * 1-based line number in the source file of the current line. Once all
     * lines have been consumed, this is the last line; for empty contents it
     * is the line before them, e.g. the line of a directive without content.
     */
    public function getCurrentLineNumber(): int
    {
        if ($this->documentIterator->isEmpty()) {
            return $this->lineOffset;
        }

        $lastKey = count($this->documentIterator->toArray()) - 1;

        return $this->getLineOffset(max(0, min($this->documentIterator->key(), $lastKey))) + 1;
    }

    /** @return array<string, int|string> */
    public function getLoggerInformation(): array
    {
        $info = [
            'currentLineNumber' => $this->getCurrentLineNumber(),
        ];
        if ($this->documentIterator->valid()) {
            $info['currentLine'] = $this->documentIterator->current();
        }

        return [...$this->documentParserContext->getLoggerInformation(), ...$info];
    }
}

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

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\QuoteNode;
use phpDocumentor\Guides\RestructuredText\Parser\Buffer;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;

use function array_values;
use function count;
use function trim;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#block-quotes
 * @implements Rule<QuoteNode>
 */
final class BlockQuoteRule implements Rule
{
    public function applies(DocumentParserContext $documentParser): bool
    {
        $isWhiteSpace = trim($documentParser->getDocumentIterator()->current()) === '';
        $isBlockLine = $this->isBlockLine($documentParser->getDocumentIterator()->getNextLine());

        return $isWhiteSpace && $isBlockLine && $documentParser->nextIndentedBlockShouldBeALiteralBlock === false;
    }

    public function apply(DocumentParserContext $documentParserContext, ?CompoundNode $on = null): ?Node
    {
        $documentIterator = $documentParserContext->getDocumentIterator();
        $buffer = new Buffer();
        $documentIterator->next();
        $indent = mb_strlen($documentIterator->current()) - mb_strlen(trim($documentIterator->current()));
        $buffer->push($documentIterator->current());

        while ($this->isBlockLine($documentIterator->getNextLine(), $indent)) {
            $documentIterator->next();
            $buffer->push($documentIterator->current());
        }

        $lines = $this->normalizeLines($this->removeLeadingWhitelines($buffer->getLines()));
        if (count($lines) === 0) {
            return null;
        }

        return new QuoteNode(
            $documentParserContext->getParser()->getSubParser()->parse(
                $documentParserContext->getContext(),
                (new Buffer($lines))->getLinesString()
            )->getChildren()
        );
    }

    private function isBlockLine(?string $line, int $minIndent = 1): bool
    {
        if ($line === null) {
            return false;
        }

        return trim($line) === '' || $this->isIndented($line, $minIndent);
    }

    /**
     * Check if line is an indented one.
     *
     * This does *not* include blank lines, use {@see isBlockLine()} to check
     * for blank or indented lines.
     *
     * @param int $minIndent can be used to require a specific level of indentation (number of spaces)
     */
    private function isIndented(string $line, int $minIndent): bool
    {
        return mb_strpos($line, str_repeat(' ', max(1, $minIndent))) === 0;
    }

    /**
     * @param string[] $lines
     *
     * @return string[]
     */
    private function removeLeadingWhitelines(array $lines): array
    {
        foreach ($lines as $index => $line) {
            if (trim($line) !== '') {
                break;
            }

            unset($lines[$index]);
        }

        return array_values($lines);
    }

    /**
     * @param string[] $lines
     * @return string[]
     */
    private function normalizeLines(array $lines): array
    {
        if ($lines !== []) {
            $firstLine = $lines[0];

            $length = strlen($firstLine);
            for ($k = 0; $k < $length; $k++) {
                if (trim($firstLine[$k]) !== '') {
                    break;
                }
            }

            foreach ($lines as &$line) {
                $line = substr($line, $k);
            }
        }

        return $lines;
    }
}

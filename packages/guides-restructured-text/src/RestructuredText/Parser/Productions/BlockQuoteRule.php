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

use phpDocumentor\Guides\Nodes\BlockNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\QuoteNode;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Parser\Buffer;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;

use function array_values;
use function count;
use function trim;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#block-quotes
 */
final class BlockQuoteRule implements Rule
{
    public function applies(DocumentParserContext $documentParser): bool
    {
        $isBlockLine = $this->isBlockLine($documentParser->getDocumentIterator()->current());

        return $isBlockLine && $documentParser->nextIndentedBlockShouldBeALiteralBlock === false;
    }

    public function apply(DocumentParserContext $documentParserContext, ?Node $on = null): ?Node
    {
        $documentIterator = $documentParserContext->getDocumentIterator();
        $buffer = new Buffer();
        $buffer->push($documentIterator->current());
        $nextLine = $documentIterator->getNextLine();

        while ($nextLine !== null && $this->isBlockLine($documentIterator->getNextLine())) {
            $documentIterator->next();
            $buffer->push($documentIterator->current());
        }

        $lines = $this->removeLeadingWhitelines($buffer->getLines());
        if (count($lines) === 0) {
            return null;
        }

        $blockNode = new BlockNode($lines);

        return new QuoteNode(
            $documentParserContext->getParser()->getSubParser()->parse(
                $documentParserContext->getContext(),
                $blockNode->getValueString()
            )
        );
    }

    private function isBlockLine(?string $line): bool
    {
        if ($line === null) {
            return false;
        }

        if ($line !== '') {
            return trim($line[0]) === '';
        }

        return trim($line) === '';
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
}

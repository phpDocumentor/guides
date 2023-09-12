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

use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\QuoteNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Buffer;

use function array_values;
use function count;
use function max;
use function mb_strlen;
use function str_repeat;
use function str_starts_with;
use function trim;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#block-quotes
 *
 * @implements Rule<QuoteNode>
 */
final class BlockQuoteRule implements Rule
{
    public const PRIORITY = 100;

    /** @param Rule<CollectionNode> $startingRule */
    public function __construct(private readonly Rule $startingRule)
    {
    }

    public function applies(BlockContext $blockContext): bool
    {
        $isWhiteSpace = trim($blockContext->getDocumentIterator()->current()) === '';
        $isBlockLine = $this->isBlockLine($blockContext->getDocumentIterator()->getNextLine());

        return $isWhiteSpace && $isBlockLine && $blockContext->getDocumentParserContext()->nextIndentedBlockShouldBeALiteralBlock === false;
    }

    public function apply(BlockContext $blockContext, CompoundNode|null $on = null): Node|null
    {
        $documentIterator = $blockContext->getDocumentIterator();
        $buffer = new Buffer();
        $documentIterator->next();
        $indent = mb_strlen($documentIterator->current()) - mb_strlen(trim($documentIterator->current()));
        $buffer->push($documentIterator->current());

        while ($this->isBlockLine($documentIterator->getNextLine(), $indent)) {
            $documentIterator->next();
            $buffer->push($documentIterator->current());
        }

        $lines = $this->removeLeadingWhitelines($buffer->getLines());
        if (count($lines) === 0) {
            return null;
        }

        $subContext = new BlockContext($blockContext->getDocumentParserContext(), (new Buffer($lines))->getLinesString());

        $collectionNode = $this->startingRule->apply($subContext);

        if ($collectionNode === null) {
            return null;
        }

        return new QuoteNode($collectionNode->getChildren());
    }

    private function isBlockLine(string|null $line, int $minIndent = 1): bool
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
        return str_starts_with($line, str_repeat(' ', max(1, $minIndent)));
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

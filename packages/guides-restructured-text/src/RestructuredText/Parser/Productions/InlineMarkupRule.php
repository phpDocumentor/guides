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
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Buffer;
use phpDocumentor\Guides\RestructuredText\Parser\InlineParser;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;

use function trim;

/**
 * The Inline markup produces inline nodes
 *
 * This rule is a special rule, as it is not capable to collect content
 * by itself. There is no way we can determine the end of an inline block.
 *
 * Just like a Paragraph the start and end are marked by white lines. But as the parent
 * can be any kind of node, the end should be marked by the caller. The best way to use this rule is
 * using a {@see Buffer buffer}. The buffer allows you to collect all lines until the end of the block and pass
 * them at once into this rule.
 *
 * ```php
 *   $buffer = new Buffer();
 *   while ($this->isEnd($blockContext->getDocumentIterator()->getNextLine()) === false) {
 *       $buffer->push($blockContext->getDocumentIterator()->current());
 *   }
 *
 *   $inlineRule = new InlineMarkupRule($spanParser);
 *   $node = $inlineRule->apply($documentParser->withContents($buffer->getLinesString()), new MyNode());
 * ```
 *
 * @implements Rule<InlineCompoundNode>
 */
final class InlineMarkupRule implements Rule
{
    public function __construct(private readonly InlineParser $inlineTokenParser)
    {
    }

    public function applies(BlockContext $blockContext): bool
    {
        return trim($blockContext->getDocumentIterator()->current()) !== '';
    }

    /**
     * @param TParent|null $on
     *
     * @return ($on is null ? InlineCompoundNode: TParent)
     *
     * @template TParent of CompoundNode
     */
    public function apply(BlockContext $blockContext, CompoundNode|null $on = null): CompoundNode|InlineCompoundNode
    {
        $documentIterator = $blockContext->getDocumentIterator();
        $buffer = $this->collectContent($documentIterator);

        $node = $this->inlineTokenParser->parse($buffer->getLinesString(), $blockContext);

        if ($on !== null) {
            $on->setValue([$node]);

            return $on;
        }

        return $node;
    }

    private function collectContent(LinesIterator $documentIterator): Buffer
    {
        $buffer = new Buffer([$documentIterator->current()]);

        while ($this->isWhiteline($documentIterator->getNextLine()) === false) {
            $documentIterator->next();
            $buffer->push($documentIterator->current());
        }

        return $buffer;
    }

    private function isWhiteline(string|null $line): bool
    {
        if ($line === null) {
            return true;
        }

        return trim($line) === '';
    }
}

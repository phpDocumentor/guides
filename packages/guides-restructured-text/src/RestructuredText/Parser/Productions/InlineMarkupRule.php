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

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\RestructuredText\Parser\Buffer;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use phpDocumentor\Guides\RestructuredText\Span\SpanParser;

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
 *   while ($this->isEnd($documentParser->getDocumentIterator()->getNextLine()) === false) {
 *       $buffer->push($documentParser->getDocumentIterator()->current());
 *   }
 *
 *   $inlineRule = new InlineMarkupRule($spanParser);
 *   $node = $inlineRule->apply($documentParser->withContents($buffer->getLinesString()), new MyNode());
 * ```
 *
 * @implements Rule<SpanNode>
 */
final class InlineMarkupRule implements Rule
{
    private SpanParser $spanParser;

    public function __construct(SpanParser $spanParser)
    {
        $this->spanParser = $spanParser;
    }

    public function applies(DocumentParserContext $documentParser): bool
    {
        return trim($documentParser->getDocumentIterator()->current()) !== '';
    }

    public function apply(DocumentParserContext $documentParserContext, ?Node $on = null): ?Node
    {
        $documentIterator = $documentParserContext->getDocumentIterator();
        $buffer = $this->collectContent($documentIterator);

        //TODO replace this parser by a rule set that can return an array of nodes which we can add
        // as child nodes to the parent.
        $node = $this->spanParser->parse($buffer->getLines(), $documentParserContext->getContext());

        if ($on !== null) {
            $on->setValue($node);
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

    private function isWhiteline(?string $line): bool
    {
        if ($line === null) {
            return true;
        }

        return trim($line) === '';
    }
}

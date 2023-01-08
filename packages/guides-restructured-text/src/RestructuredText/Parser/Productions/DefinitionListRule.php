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

use phpDocumentor\Guides\Nodes\DefinitionListNode;
use phpDocumentor\Guides\Nodes\DefinitionLists\DefinitionListItemNode;
use phpDocumentor\Guides\Nodes\DefinitionLists\DefinitionNode;
use phpDocumentor\Guides\Nodes\ListItemNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\RestructuredText\Parser\Buffer;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;

use Webmozart\Assert\Assert;
use function strpos;
use function trim;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#definition-lists
 * @implements Rule<DefinitionListNode>
 */
final class DefinitionListRule implements Rule
{
    private InlineMarkupRule $inlineMarkupRule;
    private RuleContainer $definitionProducers;

    public function __construct(InlineMarkupRule $inlineMarkupRule, RuleContainer $definitionProducers)
    {
        $this->inlineMarkupRule = $inlineMarkupRule;
        $this->definitionProducers = $definitionProducers;
    }

    public function applies(DocumentParserContext $documentParser): bool
    {
        return $this->isDefinitionTerm(
            $documentParser->getDocumentIterator()->current(),
            $documentParser->getDocumentIterator()->getNextLine()
        );
    }

    public function apply(DocumentParserContext $documentParserContext, ?Node $on = null): ?Node
    {
        $iterator = $documentParserContext->getDocumentIterator();
        $definitionListItems = [];
        while ($this->isDefinitionTerm($iterator->getNextLine(), $iterator->peek())) {
            $definitionListItems[] = $this->createListItem($documentParserContext);
            $iterator->next();
        }

        // TODO: This is a workaround because the current Main Loop in {@see DocumentParser::parseLines()} expects
        //       the cursor position to rest at the last unprocessed line, but the logic above needs is always a step
        //       'too late' in detecting whether it should have stopped
        $iterator->prev();

///        $definitionList = $this->parseDefinitionList($documentParserContext, $buffer->getLines());

        return new DefinitionListNode(... $definitionListItems);
    }

    private function createListItem(DocumentParserContext $documentParserContext): DefinitionListItemNode
    {
        $documentIterator = $documentParserContext->getDocumentIterator();
        $term = $documentIterator->current();
        $parts = explode(' : ', $term);
        $term = ltrim(array_shift($parts), '\\');
        $definitionListItem = new DefinitionListItemNode(
            $this->inlineMarkupRule->apply($documentParserContext->withContents($term)),
            array_map(
                function ($classification) use ($documentParserContext) {
                    return $this->inlineMarkupRule->apply($documentParserContext->withContents($classification));
                },
                $parts
            )
        );

        Assert::string($documentIterator->getNextLine());
        $indenting = mb_strlen($documentIterator->getNextLine()) - mb_strlen(trim($documentIterator->getNextLine()));

        while (LinesIterator::isBlockLine($documentIterator->getNextLine(), $indenting)) {
            $definitionListItem->addChildNode($this->createDefinition($documentParserContext, $indenting));
        }

        return $definitionListItem;
    }

    private function createDefinition(DocumentParserContext $documentParserContext, int $indenting): DefinitionNode
    {
        $buffer = new Buffer();
        $documentIterator = $documentParserContext->getDocumentIterator();
        while (LinesIterator::isBlockLine($documentIterator->getNextLine(), $indenting)) {
            $documentIterator->next();
            $emptyLinesBelongToDefinition = false;
            if (LinesIterator::isEmptyLine($documentIterator->current())) {
                $peek = $documentIterator->peek();
                while (LinesIterator::isEmptyLine($peek)) {
                    $peek = $documentIterator->peek();
                }

                $emptyLinesBelongToDefinition = LinesIterator::isBlockLine($peek, $indenting);
            }

            if ($emptyLinesBelongToDefinition === false && LinesIterator::isEmptyLine($documentIterator->current())) {
                break;
            }

            $buffer->push(mb_substr($documentIterator->current(), $indenting));
        }

        $node = new DefinitionNode([]);
        $nodeContext = $documentParserContext->withContents($buffer->getLinesString());
        while ($nodeContext->getDocumentIterator()->valid()) {
            $this->definitionProducers->apply($nodeContext, $node);
        }
        if (count($node->getChildren()) > 1) {
            return $node;
        }

        $nodes = $node->getChildren();
        if ($nodes[0] instanceof ParagraphNode) {
            return new DefinitionNode($nodes[0]->getChildren());
        }

        return $node;
    }

    private function isDefinitionTerm(?string $currentLine, ?string $nextLine): bool
    {
        if (LinesIterator::isEmptyLine($currentLine)) {
            return false;
        }

        if (LinesIterator::isNullOrEmptyLine($nextLine)) {
            return false;
        }

        if (LinesIterator::isBlockLine($nextLine)) {
            return true;
        }

        return false;
    }
}

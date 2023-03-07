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

    public function apply(DocumentParserContext $documentParserContext, ?CompoundNode $on = null): ?Node
    {
        $iterator = $documentParserContext->getDocumentIterator();
        $definitionListItems = [];
        do {
            $definitionListItems[] = $this->createListItem($documentParserContext);
            $iterator->next();
        } while ($this->scanForDefinitionTerm($iterator));

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
                fn($classification): SpanNode => $this->inlineMarkupRule->apply(
                    $documentParserContext->withContents($classification)
                ),
                $parts
            )
        );

        Assert::string($documentIterator->getNextLine());
        $indenting = mb_strlen($documentIterator->getNextLine()) - mb_strlen(trim($documentIterator->getNextLine()));

        while ($documentIterator->isBlockLine($documentIterator->getNextLine(), $indenting)) {
            $definitionListItem->addChildNode($this->createDefinition($documentParserContext, $indenting));
        }

        return $definitionListItem;
    }

    private function createDefinition(DocumentParserContext $documentParserContext, int $indenting): DefinitionNode
    {
        $buffer = new Buffer();
        $documentIterator = $documentParserContext->getDocumentIterator();
        while ($documentIterator->isBlockLine($documentIterator->getNextLine(), $indenting)) {
            $documentIterator->next();
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
        if (LinesIterator::isNullOrEmptyLine($currentLine)) {
            return false;
        }

        if (LinesIterator::isNullOrEmptyLine($nextLine)) {
            return false;
        }

        if (LinesIterator::isIndented($nextLine, 1)) {
            return true;
        }

        return false;
    }

    private function scanForDefinitionTerm(LinesIterator $iterator): bool
    {
        $term = $iterator->peek();
        $definition = $iterator->peek();

        if ($this->isDefinitionTerm($term, $definition)) {
            $iterator->next();
            return true;
        }

        return false;
    }
}

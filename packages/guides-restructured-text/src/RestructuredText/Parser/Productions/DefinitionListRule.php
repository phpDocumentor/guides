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
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\RestructuredText\Parser\Buffer;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use Webmozart\Assert\Assert;

use function array_map;
use function array_shift;
use function count;
use function explode;
use function ltrim;
use function mb_strlen;
use function mb_substr;
use function str_starts_with;
use function trim;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#definition-lists
 *
 * @implements Rule<DefinitionListNode>
 */
final class DefinitionListRule implements Rule
{
    public const PRIORITY = 30;

    public function __construct(private readonly InlineMarkupRule $inlineMarkupRule, private readonly RuleContainer $bodyElements)
    {
    }

    public function applies(DocumentParserContext $documentParser): bool
    {
        return $this->isDefinitionTerm(
            $documentParser->getDocumentIterator()->current(),
            $documentParser->getDocumentIterator()->getNextLine(),
        );
    }

    public function apply(DocumentParserContext $documentParserContext, CompoundNode|null $on = null): Node|null
    {
        $iterator = $documentParserContext->getDocumentIterator();
        $definitionListItems = [];
        while ($iterator->valid() && $this->isDefinitionTerm($iterator->current(), $iterator->peek())) {
            $definitionListItems[] = $this->createListItem($documentParserContext);
            $iterator->next();
        }

        // TODO: This is a workaround because the current Main Loop in {@see DocumentParser::parseLines()} expects
        //       the cursor position to rest at the last unprocessed line, but the logic above needs is always a step
        //       'too late' in detecting whether it should have stopped
        $iterator->prev();

        return new DefinitionListNode(...$definitionListItems);
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
                fn ($classification): SpanNode => $this->inlineMarkupRule->apply(
                    $documentParserContext->withContents($classification),
                ),
                $parts,
            ),
        );

        Assert::string($documentIterator->getNextLine());
        $indenting = mb_strlen($documentIterator->getNextLine()) - mb_strlen(trim($documentIterator->getNextLine()));

        while (LinesIterator::isBlockLine($documentIterator->getNextLine(), $indenting)) {
            if (LinesIterator::isEmptyLine($documentIterator->current())) {
                $documentIterator->next();
                continue;
            }

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
            $this->bodyElements->apply($nodeContext, $node);
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

    private function isDefinitionTerm(string|null $currentLine, string|null $nextLine): bool
    {
        if ($currentLine === null || LinesIterator::isEmptyLine($currentLine)) {
            return false;
        }

        // This is either a directive or a comment or an anchor. None of which are starting a definition term.
        if (str_starts_with(trim($currentLine), '.. ')) {
            return false;
        }

        // This a field list
        if (str_starts_with(trim($currentLine), ':')) {
            return false;
        }

        if (LinesIterator::isNullOrEmptyLine($nextLine)) {
            return false;
        }

        return LinesIterator::isBlockLine($nextLine);
    }
}

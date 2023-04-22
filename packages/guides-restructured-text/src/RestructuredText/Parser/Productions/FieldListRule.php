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
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\FieldListNode;
use phpDocumentor\Guides\Nodes\FieldLists\FieldListItemNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\Buffer;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\FieldListItemRule;
use RuntimeException;

use function mb_strlen;
use function mb_substr;
use function preg_match;
use function trim;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#field-lists
 *
 * @implements Rule<FieldListNode>
 */
final class FieldListRule implements Rule
{
    public const PRIORITY = 20;

    /** @param FieldListItemRule[] $fieldListItemRules */
    public function __construct(private RuleContainer $productions, private iterable $fieldListItemRules)
    {
    }

    public function applies(DocumentParserContext $documentParser): bool
    {
        return $this->isFieldLine($documentParser->getDocumentIterator()->current());
    }

    public function apply(DocumentParserContext $documentParserContext, CompoundNode|null $on = null): Node|null
    {
        $iterator = $documentParserContext->getDocumentIterator();
        $fieldListItemNodes = [];
        while ($iterator->valid() && $this->isFieldLine($iterator->current())) {
            $fieldListItemNodes[] = $this->createListItem($documentParserContext);
            $iterator->next();
        }

        if ($on instanceof DocumentNode && !$on->isTitleFound()) {
            // A field list found before the first title node is considered file wide meta data:
            // https://www.sphinx-doc.org/en/master/usage/restructuredtext/field-lists.html#file-wide-metadata
            // It is not output
            $this->addMetadata($on, $fieldListItemNodes);

            return null;
        }

        return new FieldListNode($fieldListItemNodes);
    }

    /** @param FieldListItemNode[] $fieldListItemNodes */
    private function addMetadata(DocumentNode $documentNode, array $fieldListItemNodes): void
    {
        foreach ($fieldListItemNodes as $fieldListItemNode) {
            foreach ($this->fieldListItemRules as $fieldListItemRule) {
                if (!$fieldListItemRule->applies($fieldListItemNode)) {
                    continue;
                }

                $documentNode->addHeaderNode(
                    $fieldListItemRule->apply($fieldListItemNode),
                );
            }
        }
    }

    /** @return string[] */
    private function parseField(string $line): array
    {
        if (preg_match('/^:([^:]+):( (.*)|)$/mUsi', $line, $match) > 0) {
            return [
                $match[1] ?? '',
                $match[2] ?? '',
            ];
        }

        throw new RuntimeException('No field definition found in line ' . $line);
    }

    private function createListItem(DocumentParserContext $documentParserContext): FieldListItemNode
    {
        $documentIterator = $documentParserContext->getDocumentIterator();
        [$term, $content] = $this->parseField($documentIterator->current());
        $fieldListItemNode = new FieldListItemNode(
            $term,
            trim($content),
        );

        $this->createDefinition($documentParserContext, $content, $fieldListItemNode);

        return $fieldListItemNode;
    }

    private function createDefinition(
        DocumentParserContext $documentParserContext,
        string $firstLine,
        FieldListItemNode $fieldListItemNode,
    ): void {
        $buffer = new Buffer();
        $documentIterator = $documentParserContext->getDocumentIterator();
        $nextLine = $documentIterator->getNextLine();
        if ($nextLine !== null && !$this->isFieldLine($nextLine)) {
            $indenting = mb_strlen($nextLine) - mb_strlen(trim($nextLine));
            if ($indenting > 0) {
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

                    if (
                        $emptyLinesBelongToDefinition === false
                        && LinesIterator::isEmptyLine($documentIterator->current())
                    ) {
                        break;
                    }

                    $buffer->push(mb_substr($documentIterator->current(), $indenting));
                }
            }
        }

        if ($firstLine === '' && $buffer->count() === 0) {
            return;
        }

        $nodeContext = $documentParserContext->withContents($firstLine . "\n" . $buffer->getLinesString());
        while ($nodeContext->getDocumentIterator()->valid()) {
            $this->productions->apply($nodeContext, $fieldListItemNode);
        }
    }

    private function isFieldLine(string $currentLine): bool
    {
        if (LinesIterator::isEmptyLine($currentLine)) {
            return false;
        }

        return preg_match('/^:([^:]+):( (.*)|)$/mUsi', $currentLine) > 0;
    }
}

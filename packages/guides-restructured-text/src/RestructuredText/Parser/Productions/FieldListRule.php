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
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Buffer;
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
    public function __construct(private readonly RuleContainer $productions, private readonly iterable $fieldListItemRules)
    {
    }

    public function applies(BlockContext $blockContext): bool
    {
        return self::isFieldLine($blockContext->getDocumentIterator()->current());
    }

    public function apply(BlockContext $blockContext, CompoundNode|null $on = null): Node|null
    {
        $iterator = $blockContext->getDocumentIterator();
        $fieldListItemNodes = [];
        while ($iterator->valid() && self::isFieldLine($iterator->current())) {
            $fieldListItemNodes[] = $this->createListItem($blockContext);
            $iterator->next();
            while ($iterator->valid() && LinesIterator::isEmptyLine($iterator->current())) {
                $peek = $iterator->peek();
                if (!LinesIterator::isEmptyLine($peek) && !self::isFieldLine($peek)) {
                    break;
                }

                $iterator->next();
            }
        }

        if ($on instanceof DocumentNode && !$on->isTitleFound()) {
            // A field list found before the first title node is considered file wide meta data:
            // https://www.sphinx-doc.org/en/master/usage/restructuredtext/field-lists.html#file-wide-metadata
            // It is not output
            $this->addMetadata($on, $fieldListItemNodes, $blockContext);

            return null;
        }

        return new FieldListNode($fieldListItemNodes);
    }

    /** @param FieldListItemNode[] $fieldListItemNodes */
    private function addMetadata(DocumentNode $documentNode, array $fieldListItemNodes, BlockContext $blockContext): void
    {
        foreach ($fieldListItemNodes as $fieldListItemNode) {
            foreach ($this->fieldListItemRules as $fieldListItemRule) {
                if (!$fieldListItemRule->applies($fieldListItemNode)) {
                    continue;
                }

                $metaNode = $fieldListItemRule->apply($fieldListItemNode, $blockContext);
                if ($metaNode === null) {
                    continue;
                }

                $documentNode->addHeaderNode(
                    $metaNode,
                );
            }
        }
    }

    /** @return string[] */
    private function parseField(string $line): array
    {
        if (preg_match('/^:([^:]+):( (.*)|)$/mUsi', $line, $match) > 0) {
            return [
                $match[1],
                $match[2],
            ];
        }

        throw new RuntimeException('No field definition found in line ' . $line);
    }

    private function createListItem(BlockContext $blockContext): FieldListItemNode
    {
        $documentIterator = $blockContext->getDocumentIterator();
        [$term, $content] = $this->parseField($documentIterator->current());
        $fieldListItemNode = new FieldListItemNode(
            $term,
            trim($content),
        );

        $this->createDefinition($blockContext, $content, $fieldListItemNode);

        return $fieldListItemNode;
    }

    private function createDefinition(
        BlockContext $blockContext,
        string $firstLine,
        FieldListItemNode $fieldListItemNode,
    ): void {
        $buffer = new Buffer();
        $documentIterator = $blockContext->getDocumentIterator();
        $nextLine = $documentIterator->getNextLine();
        if ($nextLine !== null && !self::isFieldLine($nextLine)) {
            $indenting = mb_strlen($nextLine) - mb_strlen(trim($nextLine));
            if ($indenting > 0) {
                $buffer->push(mb_substr($documentIterator->getNextLine() ?? '', $indenting));
                $documentIterator->next();
                while (LinesIterator::isBlockLine($documentIterator->getNextLine(), $indenting)) {
                    $emptyLinesBelongToDefinition = false;
                    if (LinesIterator::isEmptyLine($documentIterator->getNextLine())) {
                        $peek = $documentIterator->peek();
                        while (LinesIterator::isEmptyLine($peek)) {
                            $peek = $documentIterator->peek();
                        }

                        $emptyLinesBelongToDefinition = LinesIterator::isBlockLine($peek, $indenting);
                    }

                    if (
                        $emptyLinesBelongToDefinition === false
                        && LinesIterator::isEmptyLine($documentIterator->getNextLine())
                    ) {
                        break;
                    }

                    $buffer->push(mb_substr($documentIterator->getNextLine() ?? '', $indenting));
                    $documentIterator->next();
                }
            }
        }

        if ($firstLine === '' && $buffer->count() === 0) {
            return;
        }

        foreach ($buffer->getLines() as $line) {
            $fieldListItemNode->addPlaintextContentLine($line);
        }

        $subContext = new BlockContext(
            $blockContext->getDocumentParserContext(),
            $firstLine . "\n" . $buffer->getLinesString(),
            false,
            $documentIterator->key(),
        );
        while ($subContext->getDocumentIterator()->valid()) {
            $this->productions->apply($subContext, $fieldListItemNode);
        }
    }

    private static function isFieldLine(string|null $currentLine): bool
    {
        if ($currentLine === null) {
            return false;
        }

        if (LinesIterator::isEmptyLine($currentLine)) {
            return false;
        }

        return preg_match('/^:([^:]+):( (.*)|)$/mUsi', $currentLine) > 0;
    }
}

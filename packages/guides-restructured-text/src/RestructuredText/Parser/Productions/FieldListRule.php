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
use phpDocumentor\Guides\Nodes\FieldListNode;
use phpDocumentor\Guides\Nodes\FieldLists\FieldListItemNode;
use phpDocumentor\Guides\Nodes\FieldLists\FieldNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\RawNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use RuntimeException;

use function preg_match;
use function trim;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#field-lists
 *
 * @implements Rule<FieldListNode>
 */
final class FieldListRule implements Rule
{
    public function applies(DocumentParserContext $documentParser): bool
    {
        return $this->isFieldLine($documentParser->getDocumentIterator()->current());
    }

    public function apply(DocumentParserContext $documentParserContext, ?CompoundNode $on = null): ?Node
    {
        $iterator = $documentParserContext->getDocumentIterator();
        $definitionListItems = [];
        while ($iterator->valid() && $this->isFieldLine($iterator->current())) {
            $definitionListItems[] = $this->createListItem($documentParserContext);
            $iterator->next();
        }

        return new FieldListNode(...$definitionListItems);
    }

    /**
     * @return string[]
     */
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
            $term
        );

        if ($content !== '') {
            $fieldListItemNode->addChildNode(new FieldNode([new RawNode(trim($content))]));
        }

        return $fieldListItemNode;
    }

    private function isFieldLine(string $currentLine): bool
    {
        if (LinesIterator::isEmptyLine($currentLine)) {
            return false;
        }

        return preg_match('/^:([^:]+):( (.*)|)$/mUsi', $currentLine) > 0;
    }
}

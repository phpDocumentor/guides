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

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList;

use phpDocumentor\Guides\Nodes\FieldLists\FieldListItemNode;
use phpDocumentor\Guides\Nodes\ListNode;
use phpDocumentor\Guides\Nodes\Metadata\AuthorNode;
use phpDocumentor\Guides\Nodes\Metadata\AuthorsNode;
use phpDocumentor\Guides\Nodes\Metadata\MetadataNode;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\Nodes\RawNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;

use function count;
use function explode;
use function str_contains;
use function strtolower;

final class AuthorsFieldListItemRule implements FieldListItemRule
{
    public function applies(FieldListItemNode $fieldListItemNode): bool
    {
        return strtolower($fieldListItemNode->getTerm()) === 'authors';
    }

    public function apply(FieldListItemNode $fieldListItemNode, BlockContext $blockContext): MetadataNode
    {
        $authorNodes = [];
        if (count($fieldListItemNode->getChildren()) === 1) {
            $firstChild = $fieldListItemNode->getChildren()[0];

            if ($firstChild instanceof ParagraphNode) {
                // The "Authors" field may contain either: a single paragraph consisting of a list of authors,
                // separated by ";" or "," (";" is checked first, so "Doe, Jane; Doe, John" will work.)
                if (str_contains($fieldListItemNode->getPlaintextContent(), ';')) {
                    $authorStrings = explode(';', $fieldListItemNode->getPlaintextContent());
                    foreach ($authorStrings as $authorString) {
                        $authorNodes[] = new AuthorNode($authorString, [new RawNode($authorString)]);
                    }
                } elseif (str_contains($fieldListItemNode->getPlaintextContent(), ',')) {
                    $authorStrings = explode(',', $fieldListItemNode->getPlaintextContent());
                    foreach ($authorStrings as $authorString) {
                        $authorNodes[] = new AuthorNode($authorString, [new RawNode($authorString)]);
                    }
                } else {
                    $authorNodes[] = new AuthorNode($fieldListItemNode->getPlaintextContent(), $fieldListItemNode->getChildren());
                }
            }

            if ($firstChild instanceof ListNode) {
                // A bullet list whose elements each contain a single paragraph per author.
                foreach ($firstChild->getChildren() as $listItemNode) {
                    if (count($listItemNode->getChildren()) <= 0) {
                        continue;
                    }

                    $authorNodes[] = new AuthorNode(
                        '',
                        [$listItemNode->getChildren()[0]],
                    );
                }
            }
        } else {
            //  multiple paragraphs (one per author)
            foreach ($fieldListItemNode->getChildren() as $node) {
                $authorNodes[] = new AuthorNode(
                    '',
                    [$node],
                );
            }
        }

        return new AuthorsNode($authorNodes);
    }
}

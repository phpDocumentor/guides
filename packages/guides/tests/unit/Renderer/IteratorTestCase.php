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

namespace phpDocumentor\Guides\Renderer;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use PHPUnit\Framework\TestCase;

use function array_map;
use function md5;
use function shuffle;

abstract class IteratorTestCase extends TestCase
{
    protected DocumentEntryNode $entry1;
    protected DocumentEntryNode $entry2;

    /** @var DocumentNode[] */
    protected array $flatDocumentList;

    /** @var array|DocumentNode[] */
    protected array $randomOrderedDocuments;

    protected function setUp(): void
    {
        [$this->entry1, $doc1] = $this->createDocumentEntryAndNode('1.rst', '1');
        [$entry1_1, $doc1_1] = $this->createDocumentEntryAndNode('1/1.rst', '1.1');
        [$entry1_1_1, $doc1_1_1] = $this->createDocumentEntryAndNode('1/1/1.rst', '1.1.1');
        [$entry1_1_2, $doc1_1_2] = $this->createDocumentEntryAndNode('1/1/2.rst', '1.1.2');
        [$this->entry2, $doc2] = $this->createDocumentEntryAndNode('2.rst', '2');
        [$entry2_1, $doc2_1] = $this->createDocumentEntryAndNode('2/1.rst', '2.1');
        [$entry2_1_1, $doc2_1_1] = $this->createDocumentEntryAndNode('2/1/1.rst', '2.1.1');
        [$entry2_1_2, $doc2_1_2] = $this->createDocumentEntryAndNode('2/1/2.rst', '2.1.2');
        [$entry2_2, $doc2_2] = $this->createDocumentEntryAndNode('2/2.rst', '2.2');
        [$entry2_3, $doc2_3] = $this->createDocumentEntryAndNode('2/3.rst', '2.3');
        [$orphant_entry, $orphant] = $this->createDocumentEntryAndNode('orphant.rst', 'orphant');


        $this->entry1->addChild($entry1_1);
        $entry1_1->addChild($entry1_1_1);
        $entry1_1->addChild($entry1_1_2);
        $this->entry2->addChild($entry2_1);
        $entry2_1->addChild($entry2_1_1);
        $entry2_1->addChild($entry2_1_2);
        $this->entry2->addChild($entry2_2);
        $this->entry2->addChild($entry2_3);

        $this->flatDocumentList = [
            $doc1,
            $doc1_1,
            $doc1_1_1,
            $doc1_1_2,
            $doc2,
            $doc2_1,
            $doc2_1_1,
            $doc2_1_2,
            $doc2_2,
            $doc2_3,
            $orphant,
        ];

        //We shuffle the array, because input order should not matter
        $this->randomOrderedDocuments = $this->flatDocumentList;
        shuffle($this->randomOrderedDocuments);
    }

    /** @return array{DocumentEntryNode, DocumentNode} */
    private function createDocumentEntryAndNode(string $fileName, string $title): array
    {
        $titleNode = $this->createTitle($title);
        $subDocumentEntry = new DocumentEntryNode($fileName, $titleNode);
        $subDocumentNode = new DocumentNode(md5($title), $fileName);
        $subDocumentNode->setValue([new SectionNode($titleNode)]);
        $subDocumentNode->setDocumentEntry($subDocumentEntry);

        return [$subDocumentEntry, $subDocumentNode];
    }

    private function createTitle(string $title): TitleNode
    {
        return new TitleNode(InlineCompoundNode::getPlainTextInlineNode($title), 1, '1');
    }

    /**
     * @param DocumentNode[] $result
     *
     * @return (string|null)[]
     */
    protected static function documentsToTitle(array $result): array
    {
        return array_map(static fn (DocumentNode $doc) => $doc->getTitle()?->toString(), $result);
    }
}

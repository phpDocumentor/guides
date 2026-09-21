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

namespace phpDocumentor\Guides\Nodes;

use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function array_keys;

#[CoversClass(DataNode::class)]
#[CoversClass(AbstractNode::class)]
final class DataNodeTest extends TestCase
{
    public function testKeepsEachValueOnceInTheOrderItCameIn(): void
    {
        $data = new DataNode('guides-index-terms', ['beta', 'alpha', 'beta']);

        self::assertSame(['beta', 'alpha'], $data->getValues());
        self::assertSame('beta,alpha', $data->toString());
    }

    public function testMergingAddsOnlyTheValuesNotThereYet(): void
    {
        $merged = (new DataNode('terms', ['a', 'b']))->merge(new DataNode('terms', ['b', 'c']));

        self::assertSame('terms', $merged->getName());
        self::assertSame(['a', 'b', 'c'], $merged->getValues());
    }

    public function testANodeHasNoDataUntilSomeIsAttached(): void
    {
        self::assertSame([], $this->section()->getMetaData());
    }

    public function testDataUnderANameThatIsTakenIsAddedToWhatIsThere(): void
    {
        $section = $this->section();
        $section->addMetaData(new DataNode('guides-index-terms', ['installation']));
        $section->addMetaData(new DataNode('guides-index-terms', ['configuration', 'installation']));

        self::assertSame(['guides-index-terms'], array_keys($section->getMetaData()));
        self::assertSame(['installation', 'configuration'], $section->getMetaData()['guides-index-terms']->getValues());
    }

    public function testDataUnderDifferentNamesIsKeptApart(): void
    {
        $section = $this->section();
        $section->addMetaData(new DataNode('guides-index-terms', ['installation']));
        $section->addMetaData(new DataNode('pagefind-weight', ['2']));

        self::assertSame(['guides-index-terms', 'pagefind-weight'], array_keys($section->getMetaData()));
    }

    public function testDataAttachedAfterACloneIsNotSeenByTheClone(): void
    {
        $original = $this->section();
        $original->addMetaData(new DataNode('terms', ['a']));
        $clone = clone $original;

        $original->addMetaData(new DataNode('terms', ['b']));

        self::assertSame(['a'], $clone->getMetaData()['terms']->getValues());
        self::assertSame(['a', 'b'], $original->getMetaData()['terms']->getValues());
    }

    private function section(): SectionNode
    {
        return new SectionNode(new TitleNode(new InlineCompoundNode([new PlainTextInlineNode('Installation')]), 1, 'installation'));
    }
}

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

namespace phpDocumentor\Guides\Pages\Nodes;

use phpDocumentor\Guides\Nodes\DocumentNode;
use PHPUnit\Framework\TestCase;

/** @covers \phpDocumentor\Guides\Pages\Nodes\ContentTypeOverviewNode */
final class ContentTypeOverviewNodeTest extends TestCase
{
    public function testGetOutputPathReturnsConstructorValue(): void
    {
        $node = new ContentTypeOverviewNode('news/index', 'News', 'structure/content-type-overview.html.twig');

        self::assertSame('news/index', $node->getOutputPath());
    }

    public function testGetFilePathEqualsOutputPath(): void
    {
        $node = new ContentTypeOverviewNode('news/index', 'News', 'structure/content-type-overview.html.twig');

        self::assertSame('news/index', $node->getFilePath());
    }

    public function testGetPageTitleReturnsTitle(): void
    {
        $node = new ContentTypeOverviewNode('news/index', 'Latest News', 'structure/content-type-overview.html.twig');

        self::assertSame('Latest News', $node->getPageTitle());
    }

    public function testGetPageTitleReturnsNullForEmptyTitle(): void
    {
        $node = new ContentTypeOverviewNode('news/index', '', 'structure/content-type-overview.html.twig');

        self::assertNull($node->getPageTitle());
    }

    public function testGetTemplateReturnsConstructorValue(): void
    {
        $node = new ContentTypeOverviewNode('news/index', 'News', 'structure/my-custom.html.twig');

        self::assertSame('structure/my-custom.html.twig', $node->getTemplate());
    }

    public function testGetHeaderNodesReturnsEmptyArray(): void
    {
        $node = new ContentTypeOverviewNode('news/index', 'News', 'structure/content-type-overview.html.twig');

        self::assertSame([], $node->getHeaderNodes());
    }

    public function testGetChildrenReturnsEmptyArray(): void
    {
        $node = new ContentTypeOverviewNode('news/index', 'News', 'structure/content-type-overview.html.twig');

        self::assertSame([], $node->getChildren());
    }

    public function testGetItemsReturnsInjectedItems(): void
    {
        $item1 = ContentTypeItemNode::from(new DocumentNode('news/a', 'news/a'));
        $item2 = ContentTypeItemNode::from(new DocumentNode('news/b', 'news/b'));

        $node = new ContentTypeOverviewNode(
            'news/index',
            'News',
            'structure/content-type-overview.html.twig',
            [$item1, $item2],
        );

        self::assertCount(2, $node->getItems());
        self::assertSame($item1, $node->getItems()[0]);
        self::assertSame($item2, $node->getItems()[1]);
    }

    public function testGetItemsReturnsEmptyArrayByDefault(): void
    {
        $node = new ContentTypeOverviewNode('news/index', 'News', 'structure/content-type-overview.html.twig');

        self::assertSame([], $node->getItems());
    }
}

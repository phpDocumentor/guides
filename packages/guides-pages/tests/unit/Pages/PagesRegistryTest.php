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

namespace phpDocumentor\Guides\Pages;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Metadata\DateNode;
use phpDocumentor\Guides\Pages\Nodes\ContentTypeItemNode;
use phpDocumentor\Guides\Pages\Nodes\ContentTypeOverviewNode;
use phpDocumentor\Guides\Pages\Nodes\PageNode;
use PHPUnit\Framework\TestCase;

/** @covers \phpDocumentor\Guides\Pages\PagesRegistry */
final class PagesRegistryTest extends TestCase
{
    public function testAddAndGetPage(): void
    {
        $registry = new PagesRegistry();
        $page     = new PageNode('about', []);
        $registry->addPage($page);

        self::assertCount(1, $registry->getPages());
        self::assertSame($page, $registry->getPages()['about']);
    }

    public function testUpdatePagesReplacesWithPageNodeFrom(): void
    {
        $registry = new PagesRegistry();
        $page     = new PageNode('about', []);
        $registry->addPage($page);

        $doc = new DocumentNode('about', 'about');
        $registry->updatePages([$doc]);

        $pages = $registry->getPages();
        self::assertCount(1, $pages);
        self::assertInstanceOf(PageNode::class, $pages['about']);
    }

    public function testAddAndGetCollectionItems(): void
    {
        $registry = new PagesRegistry();
        $item     = ContentTypeItemNode::from(new DocumentNode('news/first', 'news/first'));
        $registry->addCollectionItem('news', $item);

        $items = $registry->getCollectionItems('news');
        self::assertCount(1, $items);
        self::assertSame($item, $items[0]);
    }

    public function testGetCollectionItemsReturnsEmptyArrayForUnknownKey(): void
    {
        $registry = new PagesRegistry();

        self::assertSame([], $registry->getCollectionItems('unknown'));
    }

    public function testGetSortedCollectionItemsSortsNewestFirst(): void
    {
        $registry = new PagesRegistry();

        $docOlder = new DocumentNode('news/older', 'news/older');
        $docOlder->addHeaderNode(new DateNode('2025-01-01'));
        $itemOlder = ContentTypeItemNode::from($docOlder);

        $docNewer = new DocumentNode('news/newer', 'news/newer');
        $docNewer->addHeaderNode(new DateNode('2026-06-01'));
        $itemNewer = ContentTypeItemNode::from($docNewer);

        $registry->addCollectionItem('news', $itemOlder);
        $registry->addCollectionItem('news', $itemNewer);

        $sorted = $registry->getSortedCollectionItems('news');

        self::assertSame('news/newer', $sorted[0]->getFilePath());
        self::assertSame('news/older', $sorted[1]->getFilePath());
    }

    public function testGetSortedCollectionItemsUndatedItemsSortLast(): void
    {
        $registry = new PagesRegistry();

        $docUndated = new DocumentNode('news/undated', 'news/undated');
        $itemUndated = ContentTypeItemNode::from($docUndated);

        $docDated = new DocumentNode('news/dated', 'news/dated');
        $docDated->addHeaderNode(new DateNode('2026-01-01'));
        $itemDated = ContentTypeItemNode::from($docDated);

        $registry->addCollectionItem('news', $itemUndated);
        $registry->addCollectionItem('news', $itemDated);

        $sorted = $registry->getSortedCollectionItems('news');

        self::assertSame('news/dated', $sorted[0]->getFilePath());
        self::assertSame('news/undated', $sorted[1]->getFilePath());
    }

    public function testGetAllRenderablesReturnsPagesAndCollectionItems(): void
    {
        $registry = new PagesRegistry();

        $page = new PageNode('about', []);
        $registry->addPage($page);

        $item = ContentTypeItemNode::from(new DocumentNode('news/first', 'news/first'));
        $registry->addCollectionItem('news', $item);

        $all = $registry->getAllRenderables();

        self::assertCount(2, $all);
        self::assertContains($page, $all);
        self::assertContains($item, $all);
    }

    public function testUpdateCollectionItemsReplacesItemByFilePath(): void
    {
        $registry = new PagesRegistry();

        $item = ContentTypeItemNode::from(new DocumentNode('news/first', 'news/first'));
        $registry->addCollectionItem('news', $item);

        $updatedDoc = new DocumentNode('news/first', 'news/first');
        $registry->updateCollectionItems('news', [$updatedDoc]);

        $items = $registry->getCollectionItems('news');
        self::assertCount(1, $items);
        self::assertInstanceOf(ContentTypeItemNode::class, $items[0]);
    }

    public function testAddOverviewStoresAndReturnsOverviewInGetAllRenderables(): void
    {
        $registry = new PagesRegistry();
        $overview = new ContentTypeOverviewNode('blog/index', 'Blog', 'blog-overview.html.twig', []);
        $registry->addOverview($overview);

        $all = $registry->getAllRenderables();

        self::assertCount(1, $all);
        self::assertContains($overview, $all);
    }
}

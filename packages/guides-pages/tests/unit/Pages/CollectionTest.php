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

use PHPUnit\Framework\TestCase;

/** @covers \phpDocumentor\Guides\Pages\Collection */
final class CollectionTest extends TestCase
{
    private const FULL_DATA = [
        'source_directory' => 'news',
        'overview_path'    => 'news/index',
        'overview_title'   => 'News',
        'overview_template' => 'structure/content-type-overview.html.twig',
        'item_template'    => 'structure/content-type-item.html.twig',
    ];

    public function testFromArraySetsAllProperties(): void
    {
        $collection = Collection::fromArray(self::FULL_DATA);

        self::assertSame('news', $collection->getSourceDirectory());
        self::assertSame('news/index', $collection->getOverviewPath());
        self::assertSame('News', $collection->getOverviewTitle());
        self::assertSame('structure/content-type-overview.html.twig', $collection->getOverviewTemplate());
        self::assertSame('structure/content-type-item.html.twig', $collection->getItemTemplate());
    }

    public function testFromArrayStripsLeadingSlashFromSourceDirectory(): void
    {
        $collection = Collection::fromArray([...self::FULL_DATA, 'source_directory' => '/news']);

        self::assertSame('news', $collection->getSourceDirectory());
    }

    public function testFromArrayStripsLeadingSlashFromOverviewPath(): void
    {
        $collection = Collection::fromArray([...self::FULL_DATA, 'overview_path' => '/news/index']);

        self::assertSame('news/index', $collection->getOverviewPath());
    }
}

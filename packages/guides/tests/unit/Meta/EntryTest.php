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

namespace phpDocumentor\Guides\Meta;

use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\Nodes\TocNode;
use PHPUnit\Framework\TestCase;

use function time;

/**
 * @coversDefaultClass \phpDocumentor\Guides\Meta\Entry
 * @covers ::<private>
 */
final class EntryTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getFile
     * @covers ::getUrl
     * @covers ::getTitle
     * @covers ::getTitles
     * @covers ::getTocs
     * @covers ::getDepends
     * @covers ::getMtime
     */
    public function testWhetherAnEntryCanBeRecorded(): void
    {
        $mtime = time();

        $file = 'example.txt';
        $url = '/docs/example.txt';
        $title = new TitleNode(new SpanNode('Example'), 1);
        $titles = [
            new TitleNode(new SpanNode('title1'), 1),
            new TitleNode(new SpanNode('title2'), 2)
        ];
        $tocs = [new TocNode(['file.txt'])];
        $depends = ['other-file.txt'];
        $links = ['another-file'];

        $entry = new Entry($file, $url, $title, $titles, $tocs, $depends, $mtime);

        self::assertSame($file, $entry->getFile());
        self::assertSame($url, $entry->getUrl());
        self::assertSame($title, $entry->getTitle());
        self::assertSame($titles, $entry->getTitles());
        self::assertSame($tocs, $entry->getTocs());
        self::assertSame($depends, $entry->getDepends());
        self::assertSame($mtime, $entry->getMtime());
    }

    /**
     * @covers ::getParent
     * @covers ::setParent
     */
    public function testSettingAParentForAMetaEntry(): void
    {
        $entry = new Entry(
            'example.txt',
            '/docs/example.txt',
            new TitleNode(new SpanNode('Example'), 1),
            [
                new TitleNode(new SpanNode('title1'), 1),
                new TitleNode(new SpanNode('title2'), 2)
            ],
            [
                new TocNode(['file.txt'])
            ],
            ['other-file.txt'],
            time()
        );

        $entry->setParent('parent');

        self::assertSame('parent', $entry->getParent());
    }
}

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

namespace phpDocumentor\Guides\MachineReadable\Toc;

use PHPUnit\Framework\TestCase;

use function array_keys;

final class PageDescriptorTest extends TestCase
{
    public function testALeafIsWrittenWithoutPages(): void
    {
        $page = new PageDescriptor('Page', ['html' => 'Page.html'], 'A page', 'a-page');

        self::assertSame(
            ['path' => 'Page', 'html' => 'Page.html', 'title' => 'A page', 'anchor' => 'a-page'],
            $page->jsonSerialize(),
        );
    }

    public function testAnOrphanSaysSoFirst(): void
    {
        $page = new PageDescriptor('Orphan', [], 'Orphan', 'orphan', true);

        self::assertSame(['orphan', 'path', 'title', 'anchor'], array_keys($page->jsonSerialize()));
    }

    public function testExtraKeysComeBeforeThePages(): void
    {
        $page = new PageDescriptor('index', [], 'Start', 'start');
        $page->addPage(new PageDescriptor('Page', [], 'A page', 'a-page'));
        $page->setExtra('permalink', 'https://example.org/start');

        self::assertSame(['path', 'title', 'anchor', 'permalink', 'pages'], array_keys($page->jsonSerialize()));
    }
}

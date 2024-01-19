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

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

use PHPUnit\Framework\TestCase;

/** @coversDefaultClass \phpDocumentor\Guides\Nodes\ListItemNode */
final class ListItemNodeTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getPrefix
     * @covers ::isOrdered
     * @covers ::getValue
     */
    public function testPrefixingCharacterTypeOfListAndContentsOfItemCanBeRecorded(): void
    {
        $contents = [
            new RawNode('contents1'),
            new RawNode('contents2'),
        ];
        $node = new ListItemNode('*', true, $contents);

        self::assertSame('*', $node->getPrefix());
        self::assertTrue($node->isOrdered());
        self::assertSame($contents, $node->getChildren());
    }
}

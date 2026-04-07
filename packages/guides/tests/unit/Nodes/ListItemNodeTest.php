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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(ListItemNode::class)]
#[CoversMethod(ListItemNode::class, '__construct')]
#[CoversMethod(ListItemNode::class, 'getPrefix')]
#[CoversMethod(ListItemNode::class, 'isOrdered')]
#[CoversMethod(ListItemNode::class, 'getValue')]
final class ListItemNodeTest extends TestCase
{
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

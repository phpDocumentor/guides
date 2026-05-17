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

#[CoversClass(SeparatorNode::class)]
#[CoversMethod(SeparatorNode::class, '__construct')]
#[CoversMethod(SeparatorNode::class, 'getLevel')]
#[CoversMethod(SeparatorNode::class, 'getValue')]
final class SeparatorNodeTest extends TestCase
{
    public function testASeparatorCanBeDefinedWithALevel(): void
    {
        $node = new SeparatorNode(2);

        self::assertSame(2, $node->getLevel());
        self::assertEmpty($node->getValue());
    }
}

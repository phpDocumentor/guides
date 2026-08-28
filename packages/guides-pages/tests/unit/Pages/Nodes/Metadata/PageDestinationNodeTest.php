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

namespace phpDocumentor\Guides\Pages\Nodes\Metadata;

use PHPUnit\Framework\TestCase;

/** @covers \phpDocumentor\Guides\Pages\Nodes\Metadata\PageDestinationNode */
final class PageDestinationNodeTest extends TestCase
{
    public function testStoresDestination(): void
    {
        $node = new PageDestinationNode('about/company');

        self::assertSame('about/company', $node->getDestination());
    }

    public function testToStringReturnsDestination(): void
    {
        $node = new PageDestinationNode('contact');

        self::assertSame('contact', $node->toString());
    }
}

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

/** @covers \phpDocumentor\Guides\Pages\Nodes\Metadata\ContentTypeTemplateNode */
final class ContentTypeTemplateNodeTest extends TestCase
{
    public function testStoresTemplatePath(): void
    {
        $node = new ContentTypeTemplateNode('structure/my-custom.html.twig');

        self::assertSame('structure/my-custom.html.twig', $node->getTemplatePath());
    }

    public function testEmptyStringReturnsEmptyPath(): void
    {
        $node = new ContentTypeTemplateNode('');

        self::assertSame('', $node->getTemplatePath());
    }

    public function testGetValueReturnsRawString(): void
    {
        $node = new ContentTypeTemplateNode('structure/item.html.twig');

        self::assertSame('structure/item.html.twig', $node->getValue());
    }
}

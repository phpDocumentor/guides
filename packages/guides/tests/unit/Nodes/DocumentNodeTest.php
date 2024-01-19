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

use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use PHPUnit\Framework\TestCase;

final class DocumentNodeTest extends TestCase
{
    public function testGetTitleReturnsFirstSectionTitle(): void
    {
        $expected = new TitleNode(new InlineCompoundNode([new PlainTextInlineNode('Test')]), 1, 'test');

        $document = new DocumentNode('test', 'file');
        $document->addChildNode(new SectionNode($expected));
        $document->addChildNode(new SectionNode(new TitleNode(new InlineCompoundNode([new PlainTextInlineNode('Test 2')]), 1, 'test-2')));

        self::assertSame($expected, $document->getTitle());
    }
}

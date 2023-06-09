<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes;

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use PHPUnit\Framework\TestCase;

final class TitleNodeTest extends TestCase
{
    public function test_it_can_be_created_with_a_title_slug_and_depth(): void
    {
        $titleNode = new InlineCompoundNode([new PlainTextInlineNode('Raw String')]);
        $node = new TitleNode($titleNode, 1, 'raw-string');
        $node->setTarget('target');

        self::assertSame('raw-string', $node->getId());
        self::assertSame([$titleNode], $node->getValue());
        self::assertSame(1, $node->getLevel());
        self::assertSame('target', $node->getTarget());
        self::assertSame('Raw String', $node->toString());
    }
}

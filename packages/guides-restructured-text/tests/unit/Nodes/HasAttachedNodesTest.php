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

namespace phpDocumentor\Guides\RestructuredText\Nodes;

use Generator;
use phpDocumentor\Guides\Nodes\HasAttachedNodes;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_keys;

final class HasAttachedNodesTest extends TestCase
{
    /** @param list<string> $names */
    #[DataProvider('provideNodes')]
    public function testNamesTheAttachedNodes(HasAttachedNodes $node, array $names): void
    {
        self::assertSame($names, array_keys($node->getAttachedNodes()));
    }

    /** @return Generator<string, array{HasAttachedNodes, list<string>}> */
    public static function provideNodes(): Generator
    {
        yield 'sidebar' => [new SidebarNode(InlineCompoundNode::getPlainTextInlineNode('title'), []), ['title']];

        yield 'confval' => [
            new ConfvalNode(
                'id',
                'id',
                InlineCompoundNode::getPlainTextInlineNode('string'),
                false,
                InlineCompoundNode::getPlainTextInlineNode('default'),
                ['scope' => InlineCompoundNode::getPlainTextInlineNode('global')],
            ),
            ['type', 'default', 'option-scope'],
        ];

        yield 'confval without type and default' => [new ConfvalNode('id', 'id'), []];

        yield 'general directive' => [
            new GeneralDirectiveNode('custom', 'argument', InlineCompoundNode::getPlainTextInlineNode('argument')),
            ['content'],
        ];
    }
}

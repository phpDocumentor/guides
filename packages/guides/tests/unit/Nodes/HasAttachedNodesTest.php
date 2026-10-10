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

use Generator;
use phpDocumentor\Guides\Nodes\DefinitionLists\DefinitionListItemNode;
use phpDocumentor\Guides\Nodes\Inline\HyperLinkNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\Nodes\Table\TableColumn;
use phpDocumentor\Guides\Nodes\Table\TableRow;
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
        yield 'definition-list item' => [
            new DefinitionListItemNode(
                InlineCompoundNode::getPlainTextInlineNode('term'),
                [InlineCompoundNode::getPlainTextInlineNode('first'), InlineCompoundNode::getPlainTextInlineNode('second')],
            ),
            ['term', 'classifier-0', 'classifier-1'],
        ];

        yield 'admonition with a title' => [
            new AdmonitionNode('note', InlineCompoundNode::getPlainTextInlineNode('title'), 'title', []),
            ['title'],
        ];

        yield 'admonition without a title' => [new AdmonitionNode('note', null, '', []), []];

        $code = new CodeNode(['code']);
        $code->setCaption(InlineCompoundNode::getPlainTextInlineNode('caption'));

        yield 'code with a caption' => [$code, ['caption']];

        yield 'code without a caption' => [new CodeNode(['code']), []];

        yield 'menu with a caption' => [
            (new TocNode([]))->withCaption(InlineCompoundNode::getPlainTextInlineNode('caption')),
            ['caption'],
        ];

        yield 'table' => [
            new TableNode(
                [new TableRow([new TableColumn('a', 1), new TableColumn('b', 1)])],
                [new TableRow([new TableColumn('header', 2)])],
            ),
            ['header-0-0', 'row-0-0', 'row-0-1'],
        ];

        $image = new ImageNode('image.png');
        $image->setTarget(new HyperLinkNode([new PlainTextInlineNode('target')], 'https://example.org'));

        yield 'image with a target' => [$image, ['target']];

        yield 'figure with a caption' => [
            new FigureNode(new ImageNode('image.png'), new ParagraphNode([InlineCompoundNode::getPlainTextInlineNode('caption')])),
            ['image', 'document'],
        ];
    }
}

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

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Compiler\CompilerContextInterface;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\DirectiveOption;
use phpDocumentor\Guides\RestructuredText\Toc\ToctreeBuilder;
use PHPUnit\Framework\TestCase;

final class ToctreeDirectiveTest extends TestCase
{
    public function testCreatesTocNodeFromRawDirectiveContent(): void
    {
        $directive = new Directive('', 'toctree', '', [
            'depth' => new DirectiveOption('depth', '2'),
            'reversed' => new DirectiveOption('reversed', null),
        ]);
        $directiveNode = new DirectiveNode($directive, rawContent: "first\nsecond\n");

        $node = (new ToctreeDirective(new ToctreeBuilder()))->createNode(
            $directiveNode,
            self::createStub(CompilerContextInterface::class),
        );

        self::assertInstanceOf(TocNode::class, $node);
        self::assertCount(2, $node->getMenuEntries());
        self::assertSame(2, $node->getDepth());
        self::assertTrue($node->isReversed());
    }

    public function testUsesParsedCaption(): void
    {
        $caption = InlineCompoundNode::getPlainTextInlineNode('Contents');
        $directive = new Directive('', 'toctree', '', [
            'caption' => new DirectiveOption('caption', 'Contents'),
        ]);
        $directive->getOption('caption')->setNode($caption);
        $directiveNode = new DirectiveNode($directive);

        $node = (new ToctreeDirective(new ToctreeBuilder()))->createNode(
            $directiveNode,
            self::createStub(CompilerContextInterface::class),
        );

        self::assertInstanceOf(TocNode::class, $node);
        self::assertSame($caption, $node->getCaption());
    }
}

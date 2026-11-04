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
use phpDocumentor\Guides\Nodes\Menu\GlobMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\NavMenuNode;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\DirectiveOption;
use phpDocumentor\Guides\RestructuredText\Toc\ToctreeBuilder;
use PHPUnit\Framework\TestCase;

final class MenuDirectiveTest extends TestCase
{
    public function testCreatesNavigationMenuFromRawDirectiveContent(): void
    {
        $directive = new Directive('', 'menu', '', [
            'depth' => new DirectiveOption('depth', '2'),
        ]);
        $directiveNode = new DirectiveNode($directive, rawContent: "page\n");

        $node = (new MenuDirective(new ToctreeBuilder()))->createNode(
            $directiveNode,
            self::createStub(CompilerContextInterface::class),
        );

        self::assertInstanceOf(NavMenuNode::class, $node);
        self::assertCount(1, $node->getMenuEntries());
        self::assertSame(2, $node->getDepth());
        self::assertTrue($node->hasOption('glob'));
        self::assertTrue($node->getOption('glob'));
    }

    public function testUsesWildcardWhenDirectiveHasNoContent(): void
    {
        $node = (new MenuDirective(new ToctreeBuilder()))->createNode(
            new DirectiveNode(new Directive('', 'menu', '')),
            self::createStub(CompilerContextInterface::class),
        );

        self::assertInstanceOf(NavMenuNode::class, $node);
        self::assertInstanceOf(GlobMenuEntryNode::class, $node->getMenuEntries()[0]);
        self::assertSame('/*', $node->getMenuEntries()[0]->getUrl());
    }
}

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
use phpDocumentor\Guides\Nodes\Menu\ContentMenuNode;
use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolverInterface;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveSourceLocation;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\DirectiveOption;
use PHPUnit\Framework\TestCase;

final class ContentsDirectiveTest extends TestCase
{
    public function testCreatesContentMenuForTheCurrentDocument(): void
    {
        $documentNameResolver = self::createMock(DocumentNameResolverInterface::class);
        $documentNameResolver
            ->expects(self::once())
            ->method('absoluteUrl')
            ->with('docs', 'docs/current')
            ->willReturn('docs/current');

        $directiveNode = new DirectiveNode(new Directive('', 'contents', '', [
            'local' => new DirectiveOption('local', null),
            'depth' => new DirectiveOption('depth', '2'),
        ]));
        $directiveNode->setSourceLocation(new DirectiveSourceLocation(
            file: 'docs/current',
            documentDirectory: 'docs',
        ));

        $node = (new ContentsDirective($documentNameResolver))->createNode(
            $directiveNode,
            self::createStub(CompilerContextInterface::class),
        );

        self::assertInstanceOf(ContentMenuNode::class, $node);
        self::assertCount(1, $node->getMenuEntries());
        self::assertSame('/docs/current', $node->getMenuEntries()[0]->getDocumentLink());
        self::assertTrue($node->isLocal());
        self::assertSame(2, $node->getDepth());
    }
}

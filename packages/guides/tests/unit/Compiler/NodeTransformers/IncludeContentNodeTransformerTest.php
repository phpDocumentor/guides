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

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\DocumentNodeTraverser;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\GenericNode;
use phpDocumentor\Guides\Nodes\IncludedContentNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use PHPUnit\Framework\TestCase;

final class IncludeContentNodeTransformerTest extends TestCase
{
    public function testIncludedContentIsInsertedAtTheIncludeLocation(): void
    {
        $before = new GenericNode('before');
        $included = new GenericNode('included');
        $after = new GenericNode('after');
        $document = new DocumentNode('foo', '/index.rst');
        $document->addChildNode($before);
        $document->addChildNode(new IncludedContentNode([$included]));
        $document->addChildNode($after);

        $actual = $this->transform($document);

        self::assertSame([$before, $included, $after], $actual->getChildren());
    }

    public function testNestedIncludedContentIsExpanded(): void
    {
        $included = new GenericNode('included');
        $document = new DocumentNode('foo', '/index.rst');
        $document->addChildNode(new IncludedContentNode([
            new IncludedContentNode([$included]),
        ]));

        $actual = $this->transform($document);

        self::assertSame([$included], $actual->getChildren());
    }

    private function transform(DocumentNode $document): DocumentNode
    {
        $transformer = new IncludeContentNodeTransformer();
        $traverser = new DocumentNodeTraverser(
            new CustomNodeTransformerFactory([$transformer]),
            $transformer->getPriority(),
        );

        return $traverser->traverse(
            $document,
            (new CompilerContext(new ProjectNode()))->withDocumentShadowTree($document),
        );
    }
}

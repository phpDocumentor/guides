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
use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\ReferenceResolvers\AnchorNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CollectLinkTargetsTransformerTest extends TestCase
{
    private AnchorNormalizer&MockObject $anchorReducer;
    private CompilerContext $context;
    private ProjectNode $projectNode;

    protected function setUp(): void
    {
        $this->projectNode = new ProjectNode('some-name');
        $this->context = $this->getCompilerContext('some-path');
        $this->anchorReducer = $this->createMock(AnchorNormalizer::class);
    }

    private function getCompilerContext(string $path): CompilerContext
    {
        $context = new CompilerContext($this->projectNode);
        $document = new DocumentNode('123', $path);
        $document = $document->setDocumentEntry(new DocumentEntryNode($path, TitleNode::emptyNode()));
        $context = $context->withDocumentShadowTree($document);

        return $context->withDocumentShadowTree($document);
    }

    public function testAnchorReducedOnRegisteringAnchor(): void
    {
        $node = new AnchorNode('some-value');

        $transformer = new CollectLinkTargetsTransformer($this->anchorReducer);

        $this->anchorReducer->expects(self::once())->method('reduceAnchor');
        $transformer->enterNode($node, $this->context);
    }
}

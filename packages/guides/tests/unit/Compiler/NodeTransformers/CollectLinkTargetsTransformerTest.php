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
use phpDocumentor\Guides\ReferenceResolvers\NullAnchorNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;

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

    public function testAnchorDeclaredTwiceInADocumentIsADuplicate(): void
    {
        $logger = new TestLogger();
        $transformer = new CollectLinkTargetsTransformer(new NullAnchorNormalizer(), $logger);

        $this->compileDocument($transformer, ['examples', 'examples']);

        self::assertTrue($logger->hasWarningThatContains('Duplicate anchor "examples"'));
    }

    public function testAnchorsOfADocumentCompiledAgainAreNoDuplicates(): void
    {
        $logger = new TestLogger();
        $transformer = new CollectLinkTargetsTransformer(new NullAnchorNormalizer(), $logger);

        // As the dev server compiles a changed document again, while the project keeps its anchors
        $this->compileDocument($transformer, ['examples', 'more-examples']);
        $this->compileDocument($transformer, ['examples', 'more-examples']);

        self::assertFalse($logger->hasWarningRecords());
    }

    public function testAnchorDeclaredInAnotherDocumentIsADuplicate(): void
    {
        $logger = new TestLogger();
        $transformer = new CollectLinkTargetsTransformer(new NullAnchorNormalizer(), $logger);

        $this->compileDocument($transformer, ['examples']);
        $this->compileDocument($transformer, ['examples'], $this->getCompilerContext('other-path'));

        self::assertTrue($logger->hasWarningThatContains(
            'Duplicate anchor "examples". There is already another anchor of that name in document "some-path"',
        ));
    }

    /** @param list<string> $anchors */
    private function compileDocument(
        CollectLinkTargetsTransformer $transformer,
        array $anchors,
        CompilerContext|null $context = null,
    ): void {
        $context ??= $this->context;
        $document = $context->getDocumentNode();
        $transformer->enterNode($document, $context);
        foreach ($anchors as $anchor) {
            $transformer->enterNode(new AnchorNode($anchor), $context);
        }

        $transformer->leaveNode($document, $context);
    }
}

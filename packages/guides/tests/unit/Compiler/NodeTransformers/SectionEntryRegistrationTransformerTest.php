<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\DocumentTree\SectionEntryNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SectionEntryRegistrationTransformerTest extends TestCase
{
    private CompilerContext $context;

    protected function setUp(): void
    {
        $this->context = self::getCompilerContext('some/path');
    }

    private static function getCompilerContext(string $path): CompilerContext
    {
        $context = new CompilerContext(new ProjectNode());
        $document = new DocumentNode('123', $path);
        $document = $document->withDocumentEntry(new DocumentEntryNode($path, TitleNode::emptyNode()));

        return $context->withShadowTree($document);
    }

    public function testSectionGetsRegistered(): void
    {
        $node = new SectionNode(TitleNode::emptyNode());

        $transformer = new SectionEntryRegistrationTransformer();

        $transformer->enterNode($node, $this->context);
        $transformer->leaveNode($node, $this->context);
        self::assertCount(1, $this->context->getDocumentNode()->getDocumentEntry()->getSections());
        self::assertInstanceOf(SectionEntryNode::class, $this->context->getDocumentNode()->getDocumentEntry()->getSections()[0]);
    }
}

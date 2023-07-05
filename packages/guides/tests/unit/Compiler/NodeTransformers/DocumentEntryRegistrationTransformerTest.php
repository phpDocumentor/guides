<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DocumentEntryRegistrationTransformerTest extends TestCase
{
    private CompilerContext $context;

    protected function setUp(): void
    {
        $this->context = self::getCompilerContext('some/path');
    }

    private static function getCompilerContext(string $path): CompilerContext
    {
        $context = new CompilerContext(new ProjectNode());

        return $context->withShadowTree(new DocumentNode('123', $path));
    }

    public function testLeaveNodeWillReturnDocumentNodeWithEntry(): void
    {
        $node = new DocumentNode('', '');
        $node->setValue([new SectionNode(TitleNode::emptyNode())]);
        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockLogger->expects($this->never())->method('warning');
        $mockLogger->expects($this->never())->method('error');

        $transformer = new DocumentEntryRegistrationTransformer($mockLogger);

        $result = $transformer->leaveNode($node, $this->context);
        self::assertInstanceOf(DocumentNode::class, $result);
        self::assertInstanceOf(DocumentEntryNode::class, $result->getDocumentEntry());
    }

    public function testDocumentWithoutTitleCausesWarning(): void
    {
        $node = new DocumentNode('', '');
        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockLogger->expects($this->once())->method('warning');

        $transformer = new DocumentEntryRegistrationTransformer($mockLogger);

        $result = $transformer->leaveNode($node, $this->context);
        self::assertInstanceOf(DocumentNode::class, $result);
        self::assertInstanceOf(DocumentEntryNode::class, $result->getDocumentEntry());
    }
}

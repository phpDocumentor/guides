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
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\DocumentTree\SectionEntryNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use PHPUnit\Framework\TestCase;

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
        $document = $document->setDocumentEntry(new DocumentEntryNode($path, TitleNode::emptyNode()));

        return $context->withDocumentShadowTree($document);
    }

    public function testSectionGetsRegistered(): void
    {
        $node = new SectionNode(TitleNode::emptyNode());
        $node2 = new SectionNode(TitleNode::emptyNode());

        $transformer = new SectionEntryRegistrationTransformer();

        $transformer->enterNode($node, $this->context);
        $transformer->enterNode($node2, $this->context);
        $transformer->leaveNode($node2, $this->context);
        $transformer->leaveNode($node, $this->context);
        self::assertCount(1, $this->context->getDocumentNode()->getDocumentEntry()->getSections());
        self::assertInstanceOf(SectionEntryNode::class, $this->context->getDocumentNode()->getDocumentEntry()->getSections()[0]);
    }
}

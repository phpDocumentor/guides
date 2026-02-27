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

namespace phpDocumentor\Guides\ReferenceResolvers;

use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\Inline\HyperLinkNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\UrlGenerator\UrlGeneratorInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class PageHyperlinkResolverTest extends TestCase
{
    private RenderContext&MockObject $renderContext;
    private ProjectNode $projectNode;
    private MockObject&UrlGeneratorInterface $urlGenerator;
    private MockObject&DocumentNameResolverInterface $documentNameResolver;
    private PageHyperlinkResolver $subject;

    protected function setUp(): void
    {
        $documentEntry = new DocumentEntryNode('some-document', TitleNode::emptyNode());
        $this->projectNode = new ProjectNode('some-name');
        $this->projectNode->addDocumentEntry($documentEntry);
        $this->renderContext = $this->createMock(RenderContext::class);
        $this->renderContext->expects(self::any())->method('getProjectNode')->willReturn($this->projectNode);
        $this->renderContext->method('getDirName')->willReturn('');
        $this->renderContext->method('getOutputFormat')->willReturn('html');
        $this->documentNameResolver = self::createMock(DocumentNameResolverInterface::class);
        $this->urlGenerator = self::createMock(UrlGeneratorInterface::class);
        $this->subject = new PageHyperlinkResolver($this->urlGenerator, $this->documentNameResolver);
    }

    #[DataProvider('pathProvider')]
    public function testPageHyperlinkResolver(string $expected, string $input, string $path): void
    {
        $this->documentNameResolver->expects(self::once())->method('canonicalUrl')->with('', $path)->willReturn($path);
        $node = new HyperLinkNode([], $input);
        $this->urlGenerator->expects(self::once())->method('generateCanonicalOutputUrl')->willReturn($path);
        $messages = new Messages();
        self::assertTrue($this->subject->resolve($node, $this->renderContext, $messages));
        self::assertEmpty($messages->getWarnings());
        self::assertEquals($expected, $node->getUrl());
    }

    public function testDocumentNotFound(): void
    {
        $this->documentNameResolver->expects(self::once())->method('canonicalUrl')->with('', 'nonexistent-page')->willReturn('nonexistent-page');
        $node = new HyperLinkNode([], 'nonexistent-page#anchor');
        $messages = new Messages();
        self::assertFalse($this->subject->resolve($node, $this->renderContext, $messages));
        self::assertEquals('', $node->getUrl());
    }

    /** @return string[][] */
    public static function pathProvider(): array
    {
        return [
            'plain' => [
                'expected' => 'some-document',
                'input' => 'some-document',
                'path' => 'some-document',
            ],
            'withAnchor' => [
                'expected' => 'some-document#anchor',
                'input' => 'some-document#anchor',
                'path' => 'some-document',
            ],
        ];
    }
}

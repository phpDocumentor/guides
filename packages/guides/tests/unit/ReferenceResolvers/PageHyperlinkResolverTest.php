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
use phpDocumentor\Guides\Settings\ProjectSettings;
use phpDocumentor\Guides\Settings\SettingsManager;
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

    #[DataProvider('provideLinksToAPageOfAnotherInputFormat')]
    public function testLinkWithTheExtensionOfAnotherInputFormatFindsThePage(string $input, string $path, string $expected): void
    {
        $subject = new PageHyperlinkResolver($this->urlGenerator, $this->documentNameResolver, $this->settingsWithInputFormat('rst,md'));
        $this->documentNameResolver->expects(self::once())->method('canonicalUrl')->with('', $path)->willReturn($path);
        $this->urlGenerator->expects(self::once())->method('generateCanonicalOutputUrl')->with($this->renderContext, 'some-document')->willReturn('some-document.html');
        $node = new HyperLinkNode([], $input);

        self::assertTrue($subject->resolve($node, $this->renderContext, new Messages()));
        self::assertSame($expected, $node->getUrl());
    }

    /** @return iterable<string, array{input: string, path: string, expected: string}> */
    public static function provideLinksToAPageOfAnotherInputFormat(): iterable
    {
        yield 'reStructuredText page from Markdown' => [
            'input' => 'some-document.rst',
            'path' => 'some-document.rst',
            'expected' => 'some-document.html',
        ];

        yield 'Markdown page from reStructuredText, with anchor' => [
            'input' => 'some-document.md#anchor',
            'path' => 'some-document.md',
            'expected' => 'some-document.html#anchor',
        ];
    }

    /** With one input format, a link that still has an extension is resolved as before this lookup existed. */
    #[DataProvider('provideLinksWithAnExtension')]
    public function testLinkWithAnExtensionIsNotAPageWithOneInputFormat(string $link): void
    {
        $subject = new PageHyperlinkResolver($this->urlGenerator, $this->documentNameResolver, $this->settingsWithInputFormat('rst'));
        $this->documentNameResolver->expects(self::once())->method('canonicalUrl')->with('', $link)->willReturn($link);
        $this->urlGenerator->expects(self::never())->method('generateCanonicalOutputUrl');
        $node = new HyperLinkNode([], $link);

        self::assertFalse($subject->resolve($node, $this->renderContext, new Messages()));
    }

    /** @return iterable<string, array{string}> */
    public static function provideLinksWithAnExtension(): iterable
    {
        yield 'extension of the input format' => ['some-document.rst'];
        yield 'extension of another format' => ['some-document.md'];
    }

    public function testLinkWithAnExtensionThatIsNoInputFormatIsNotAPage(): void
    {
        $subject = new PageHyperlinkResolver($this->urlGenerator, $this->documentNameResolver, $this->settingsWithInputFormat('rst,md'));
        $this->documentNameResolver->expects(self::once())->method('canonicalUrl')->with('', 'some-document.txt')->willReturn('some-document.txt');
        $this->urlGenerator->expects(self::never())->method('generateCanonicalOutputUrl');
        $node = new HyperLinkNode([], 'some-document.txt');

        self::assertFalse($subject->resolve($node, $this->renderContext, new Messages()));
    }

    private function settingsWithInputFormat(string $inputFormat): SettingsManager
    {
        $settings = new ProjectSettings();
        $settings->setInputFormat($inputFormat);

        return new SettingsManager($settings);
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
            'withMultipleHashes' => [
                'expected' => 'some-document#anchor#extra',
                'input' => 'some-document#anchor#extra',
                'path' => 'some-document',
            ],
        ];
    }
}

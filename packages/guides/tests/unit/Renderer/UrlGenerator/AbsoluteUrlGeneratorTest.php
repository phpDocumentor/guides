<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Renderer\UrlGenerator;

use League\Flysystem\FilesystemInterface;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolver;
use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolverInterface;
use phpDocumentor\Guides\RenderContext;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function md5;

final class AbsoluteUrlGeneratorTest extends TestCase
{
    #[DataProvider('generateAbsoluteInternalUrlProvider')]
    public function testGenerateAbsoluteInternalUrl(string $expected, string $canonicalUrl, string $destinationPath): void
    {
        $urlGenerator = new AbsoluteUrlGenerator(self::createStub(DocumentNameResolverInterface::class));
        $renderContext = $this->createMock(RenderContext::class);
        $renderContext->method('getDestinationPath')->willReturn($destinationPath);
        self::assertSame($expected, $urlGenerator->generateInternalPathFromRelativeUrl($renderContext, $canonicalUrl));
    }

    /** @return array<string, array<string, string|null>> */
    public static function generateAbsoluteInternalUrlProvider(): array
    {
        return [
            'Destination path absolute' => [
                'expected' => '/guides/file.html',
                'canonicalUrl' => 'file.html',
                'destinationPath' => '/guides',
            ],
            'Destination path relative' => [
                'expected' => 'guides/dir/file.html',
                'canonicalUrl' => 'dir/file.html',
                'destinationPath' => 'guides',
            ],
            'Destination path relative slash at end' => [
                'expected' => 'guides/dir/file.html',
                'canonicalUrl' => 'dir/file.html',
                'destinationPath' => 'guides/',
            ],
            'Destination Path Empty' => [
                'expected' => 'dir/file.html',
                'canonicalUrl' => 'dir/file.html',
                'destinationPath' => '',
            ],
            'Destination Path Slash' => [
                'expected' => '/dir/file.html',
                'canonicalUrl' => 'dir/file.html',
                'destinationPath' => '/',
            ],
        ];
    }

    #[DataProvider('documentPathProvider')]
    public function testRelativeDocUrl(
        string $filePath,
        string $destinationPath,
        string $linkedDocument,
        string $result,
        string|null $anchor = null,
    ): void {
        $urlGenerator = new AbsoluteUrlGenerator(new DocumentNameResolver());
        $documentNode = new DocumentNode(md5('hash'), $filePath);

        $projectNode = new ProjectNode();
        $projectNode->addDocumentEntry(new DocumentEntryNode(
            'getting-started/configuration',
            TitleNode::emptyNode(),
        ));

        $context = RenderContext::forDocument(
            $documentNode,
            [$documentNode],
            self::createStub(FilesystemInterface::class),
            self::createStub(FilesystemInterface::class),
            $destinationPath,
            'txt',
            $projectNode,
        );

        self::assertSame($result, $urlGenerator->generateCanonicalOutputUrl($context, $linkedDocument, $anchor));
    }

    /** @return string[][] */
    public static function documentPathProvider(): array
    {
        return [
            [
                'filePath' => 'getting-started/configuration',
                'destinationPath' => 'guide/',
                'linkedDocument' => 'installing',
                'result' => 'guide/getting-started/installing.txt',
            ],
            [
                'filePath' => 'getting-started/configuration',
                'destinationPath' => 'guide/',
                'linkedDocument' => '/installing',
                'result' => 'guide/installing.txt',
            ],
            [
                'filePath' => 'getting-started/configuration',
                'destinationPath' => 'guide',
                'linkedDocument' => 'getting-started/configuration',
                'result' => 'guide/getting-started/configuration.txt#composer',
                'anchor' => 'composer',
            ],
            [
                'filePath' => 'getting-started/configuration',
                'destinationPath' => 'guide/',
                'linkedDocument' => '../references/installing',
                'result' => 'guide/references/installing.txt',
            ],
        ];
    }
}

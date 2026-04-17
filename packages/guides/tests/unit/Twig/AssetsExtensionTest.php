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

namespace phpDocumentor\Guides\Twig;

use League\Flysystem\FilesystemInterface;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolverInterface;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\UrlGenerator\UrlGeneratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class AssetsExtensionTest extends TestCase
{
    private DocumentNameResolverInterface&MockObject $documentNameResolver;
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private LoggerInterface&MockObject $logger;
    private AssetsExtension $extension;

    protected function setUp(): void
    {
        $this->documentNameResolver = self::createMock(DocumentNameResolverInterface::class);
        $this->urlGenerator = self::createMock(UrlGeneratorInterface::class);
        $this->logger = self::createMock(LoggerInterface::class);
        $this->extension = new AssetsExtension(
            $this->logger,
            self::createMock(NodeRenderer::class),
            $this->documentNameResolver,
            $this->urlGenerator,
        );
    }

    private function stubRenderContext(): RenderContext&MockObject
    {
        $renderContext = self::createMock(RenderContext::class);
        $renderContext->method('getDirName')->willReturn('docs/cli');
        $renderContext->method('getDestinationPath')->willReturn('/guides');
        $this->documentNameResolver->method('canonicalUrl')
            ->willReturn('docs/cli/first-docs.png');
        $this->documentNameResolver->method('absoluteUrl')
            ->willReturn('/guides/docs/cli/first-docs.png');
        $this->urlGenerator->method('generateInternalUrl')
            ->willReturn('docs/cli/first-docs.png');

        return $renderContext;
    }

    /** @link https://github.com/phpDocumentor/phpDocumentor/issues/3635 */
    public function testAssetSkipsCopyWhenDestinationAlreadyHasTheFile(): void
    {
        $renderContext = $this->stubRenderContext();
        $origin = self::createMock(FilesystemInterface::class);
        $destination = self::createMock(FilesystemInterface::class);
        $renderContext->method('getOrigin')->willReturn($origin);
        $renderContext->method('getDestination')->willReturn($destination);

        $origin->method('has')->with('docs/cli/first-docs.png')->willReturn(true);
        $destination->method('has')->with('/guides/docs/cli/first-docs.png')->willReturn(true);

        $origin->expects(self::never())->method('read');
        $destination->expects(self::never())->method('put');
        $this->logger->expects(self::never())->method('error');

        $this->extension->asset(['env' => $renderContext], 'first-docs.png');
    }

    public function testAssetCopiesWhenDestinationDoesNotHaveTheFile(): void
    {
        $renderContext = $this->stubRenderContext();
        $origin = self::createMock(FilesystemInterface::class);
        $destination = self::createMock(FilesystemInterface::class);
        $renderContext->method('getOrigin')->willReturn($origin);
        $renderContext->method('getDestination')->willReturn($destination);

        $origin->method('has')->with('docs/cli/first-docs.png')->willReturn(true);
        $origin->method('read')->with('docs/cli/first-docs.png')->willReturn('PNG BYTES');
        $destination->method('has')->with('/guides/docs/cli/first-docs.png')->willReturn(false);

        $destination->expects(self::once())
            ->method('put')
            ->with('/guides/docs/cli/first-docs.png', 'PNG BYTES')
            ->willReturn(true);
        $this->logger->expects(self::never())->method('error');

        $this->extension->asset(['env' => $renderContext], 'first-docs.png');
    }

    /** @link https://github.com/phpDocumentor/phpDocumentor/issues/3635 */
    public function testAssetIsIdempotentAcrossConsecutiveCalls(): void
    {
        $renderContext = $this->stubRenderContext();
        $origin = self::createMock(FilesystemInterface::class);
        $destination = self::createMock(FilesystemInterface::class);
        $renderContext->method('getOrigin')->willReturn($origin);
        $renderContext->method('getDestination')->willReturn($destination);

        $origin->method('has')->with('docs/cli/first-docs.png')->willReturn(true);
        $origin->expects(self::once())
            ->method('read')
            ->with('docs/cli/first-docs.png')
            ->willReturn('PNG BYTES');
        $destination->method('has')
            ->with('/guides/docs/cli/first-docs.png')
            ->willReturnOnConsecutiveCalls(false, true);

        $destination->expects(self::once())
            ->method('put')
            ->with('/guides/docs/cli/first-docs.png', 'PNG BYTES')
            ->willReturn(true);
        $this->logger->expects(self::never())->method('error');

        $this->extension->asset(['env' => $renderContext], 'first-docs.png');
        $this->extension->asset(['env' => $renderContext], 'first-docs.png');
    }
}

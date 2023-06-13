<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Event;

use League\Flysystem\FilesystemInterface;
use phpDocumentor\Guides\Handlers\RenderDocumentCommand;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\UrlGeneratorInterface;
use PHPUnit\Framework\TestCase;

class PostRenderDocumentTest extends TestCase
{
    public function testEventCreation(): void
    {
        $document = new DocumentNode('hash', 'path');
        $command = new RenderDocumentCommand(
            $document,
            RenderContext::forDocument(
                $document,
                $this->createMock(FilesystemInterface::class),
                $this->createMock(FilesystemInterface::class),
                '/path',
                $this->createMock(UrlGeneratorInterface::class),
                'html',
                new ProjectNode(),
            ),
        );

        $renderer = $this->createMock(NodeRenderer::class);

        $event = new PostRenderDocument(
            $renderer,
            $command,
        );

        self::assertSame($command, $event->getCommand());
        self::assertSame($renderer, $event->getRenderer());
    }
}

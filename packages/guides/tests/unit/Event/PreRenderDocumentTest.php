<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Event;

use League\Flysystem\FilesystemInterface;
use phpDocumentor\Guides\Handlers\RenderDocumentCommand;
use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\UrlGeneratorInterface;
use PHPUnit\Framework\TestCase;

class PreRenderDocumentTest extends TestCase
{
    public function testCreateEvent(): void
    {
        $document = new DocumentNode('hash', 'path');
        $command = new RenderDocumentCommand(
            $document,
            RenderContext::forDocument(
                $document,
                $this->createMock(FilesystemInterface::class),
                $this->createMock(FilesystemInterface::class),
                '/path',
                new Metas(),
                $this->createMock(UrlGeneratorInterface::class),
                'html',
            ),
        );

        $renderer = $this->createMock(NodeRenderer::class);

        $event = new PreRenderDocument(
            $renderer,
            $command,
        );

        self::assertSame($command, $event->getCommand());
        self::assertSame($renderer, $event->getRenderer());
    }
}

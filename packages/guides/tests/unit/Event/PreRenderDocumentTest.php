<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Event;

use League\Flysystem\FilesystemInterface;
use phpDocumentor\Guides\Handlers\RenderDocumentCommand;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Settings\SettingsManager;
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
                [$document],
                $this->createMock(FilesystemInterface::class),
                $this->createMock(FilesystemInterface::class),
                '/path',
                $this->createMock(UrlGeneratorInterface::class),
                'html',
                new ProjectNode(),
                self::createStub(SettingsManager::class),
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

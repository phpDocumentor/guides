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

namespace phpDocumentor\Guides\Event;

use League\Flysystem\FilesystemInterface;
use phpDocumentor\Guides\Handlers\RenderCommand;
use phpDocumentor\Guides\Handlers\RenderHandler;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\Renderer\TypeRendererFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

final class PreRenderProcessTest extends TestCase
{
    private EventDispatcherInterface&MockObject $eventDispatcherMock;

    protected function setUp(): void
    {
        $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
    }

    private function createRenderCommand(): RenderCommand
    {
        $documentEntry = new DocumentEntryNode('file', TitleNode::emptyNode(), true);
        $document = new DocumentNode('hash', 'file');
        $document->setDocumentEntry($documentEntry);
        $projectNode =  new ProjectNode();
        $projectNode->setDocumentEntries([$documentEntry]);

        return new RenderCommand(
            'html',
            [$document],
            $this->createMock(FilesystemInterface::class),
            $this->createMock(FilesystemInterface::class),
            $projectNode,
        );
    }

    private function createRenderHandler(): RenderHandler
    {
        return new RenderHandler($this->createMock(TypeRendererFactory::class), $this->eventDispatcherMock);
    }

    public function testCreateEvent(): void
    {
        $command = $this->createRenderCommand();
        $event = new PreRenderProcess(
            $command,
        );

        self::assertSame($command, $event->getCommand());
        self::assertFalse($event->isExitRendering());
        $event->setExitRendering(true);
        self::assertTrue($event->isExitRendering());
    }

    public function testEventIsCalled(): void
    {
        $command = $this->createRenderCommand();
        $preRenderEvent = new PreRenderProcess($command);
        $postRenderEvent = new PostRenderProcess($command);
        $this->eventDispatcherMock
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->willReturnOnConsecutiveCalls(
                $preRenderEvent,
                $postRenderEvent,
            );
        $renderHandler = $this->createRenderHandler();
        $renderHandler->handle($command);
    }
}

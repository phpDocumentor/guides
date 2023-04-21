<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Handlers;

use phpDocumentor\Guides\Event\PostRenderDocument;
use phpDocumentor\Guides\Event\PreRenderDocument;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\DocumentNode;
use Psr\EventDispatcher\EventDispatcherInterface;

use function assert;

final class RenderDocumentHandler
{
    /** @var NodeRenderer<DocumentNode> */
    private NodeRenderer $renderer;

    /** @var EventDispatcherInterface */
    private EventDispatcherInterface $eventDispatcher;

    /**
     * @param NodeRenderer<DocumentNode> $renderer
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        NodeRenderer $renderer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->renderer = $renderer;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handle(RenderDocumentCommand $command): void
    {
        $preRenderDocumentEvent = $this->eventDispatcher->dispatch(
            new PreRenderDocument($this->renderer, $command)
        );
        assert($preRenderDocumentEvent instanceof PreRenderDocument);

        $command->getContext()->getDestination()->put(
            $command->getFileDestination(),
            $this->renderer->render(
                $command->getDocument(),
                $command->getContext()
            )
        );

        $postRenderDocumentEvent = $this->eventDispatcher->dispatch(
            new PostRenderDocument($this->renderer, $command)
        );
        assert($postRenderDocumentEvent instanceof PostRenderDocument);
    }
}

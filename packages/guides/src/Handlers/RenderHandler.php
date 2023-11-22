<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Handlers;

use phpDocumentor\Guides\Event\PostRenderProcess;
use phpDocumentor\Guides\Renderer\TypeRendererFactory;
use Psr\EventDispatcher\EventDispatcherInterface;

use function assert;

final class RenderHandler
{
    public function __construct(
        private readonly TypeRendererFactory $renderSetFactory,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function handle(RenderCommand $command): void
    {
        $renderSet = $this->renderSetFactory->getRenderSet($command->getOutputFormat());
        $renderSet->render($command);
        $postRenderProcessEvent = $this->eventDispatcher->dispatch(
            new PostRenderProcess($command),
        );
        assert($postRenderProcessEvent instanceof PostRenderProcess);
    }
}

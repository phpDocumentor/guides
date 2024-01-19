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

namespace phpDocumentor\Guides\Handlers;

use phpDocumentor\Guides\Event\PostRenderProcess;
use phpDocumentor\Guides\Event\PreRenderProcess;
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
        $preRenderProcessEvent = $this->eventDispatcher->dispatch(
            new PreRenderProcess($command),
        );
        assert($preRenderProcessEvent instanceof PreRenderProcess);
        if ($preRenderProcessEvent->isExitRendering()) {
            return;
        }

        $renderSet = $this->renderSetFactory->getRenderSet($command->getOutputFormat());
        $renderSet->render($command);
        $postRenderProcessEvent = $this->eventDispatcher->dispatch(
            new PostRenderProcess($command),
        );
        assert($postRenderProcessEvent instanceof PostRenderProcess);
    }
}

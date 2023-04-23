<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Handlers;

use phpDocumentor\Guides\Renderer\TypeRendererFactory;

final class RenderHandler
{
    public function __construct(private readonly TypeRendererFactory $renderSetFactory)
    {
    }

    public function handle(RenderCommand $command): void
    {
        $renderSet = $this->renderSetFactory->getRenderSet($command->getOutputFormat());
        $renderSet->render($command);
    }
}

<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Handlers;

use phpDocumentor\Guides\Renderer;

final class RenderDocumentHandler
{
    private Renderer $renderer;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function handle(RenderDocumentCommand $command): void
    {
        $command->getContext()->getDestination()->put(
            $command->getFileDestination(),
            $this->renderer->renderNode(
                $command->getDocument(),
                $command->getContext()
            )
        );
    }
}

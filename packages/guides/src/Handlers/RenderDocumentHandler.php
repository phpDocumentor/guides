<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Handlers;

use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\TemplateRenderer;

final class RenderDocumentHandler
{
    private NodeRenderer $renderer;

    public function __construct(NodeRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function handle(RenderDocumentCommand $command): void
    {
        $command->getContext()->getDestination()->put(
            $command->getFileDestination(),
            $this->renderer->render(
                $command->getDocument(),
                $command->getContext()
            )
        );
    }
}

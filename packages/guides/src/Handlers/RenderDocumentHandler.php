<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Handlers;

use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\DocumentNode;

final class RenderDocumentHandler
{
    /** @param NodeRenderer<DocumentNode> $renderer */
    public function __construct(private NodeRenderer $renderer)
    {
    }

    public function handle(RenderDocumentCommand $command): void
    {
        $command->getContext()->getDestination()->put(
            $command->getFileDestination(),
            $this->renderer->render(
                $command->getDocument(),
                $command->getContext(),
            ),
        );
    }
}

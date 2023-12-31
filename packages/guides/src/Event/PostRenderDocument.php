<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Event;

use phpDocumentor\Guides\Handlers\RenderDocumentCommand;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\DocumentNode;

/**
 * This event is called after the rendering of each document.
 *
 * It can for example be used to display a progress bar or to post-process the rendered documents one by one.
 */
final class PostRenderDocument
{
    /** @param NodeRenderer<DocumentNode> $renderer */
    public function __construct(
        private readonly NodeRenderer $renderer,
        private readonly RenderDocumentCommand $command,
    ) {
    }

    /** @return NodeRenderer<DocumentNode> */
    public function getRenderer(): NodeRenderer
    {
        return $this->renderer;
    }

    public function getCommand(): RenderDocumentCommand
    {
        return $this->command;
    }
}

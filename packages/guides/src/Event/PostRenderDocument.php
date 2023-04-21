<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Event;

use phpDocumentor\Guides\Handlers\RenderDocumentCommand;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\DocumentNode;

final class PostRenderDocument
{
    /** @var NodeRenderer<DocumentNode> */
    private NodeRenderer $renderer;

    private RenderDocumentCommand $command;

    /**
     * @param NodeRenderer<DocumentNode> $renderer
     * @param RenderDocumentCommand $command
     */
    public function __construct(NodeRenderer $renderer, RenderDocumentCommand $command)
    {
        $this->renderer = $renderer;
        $this->command = $command;
    }

    /**
     * @return NodeRenderer<DocumentNode>
     */
    public function getRenderer(): NodeRenderer
    {
        return $this->renderer;
    }

    public function getCommand(): RenderDocumentCommand
    {
        return $this->command;
    }
}

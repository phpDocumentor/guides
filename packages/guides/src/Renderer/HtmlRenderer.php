<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Renderer;

use League\Tactician\CommandBus;
use phpDocumentor\Guides\Handlers\RenderCommand;
use phpDocumentor\Guides\Handlers\RenderDocumentCommand;
use phpDocumentor\Guides\Handlers\RenderDocumentHandler;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\UrlGenerator;

class HtmlRenderer implements TypeRenderer
{
    public const TYPE = 'html';

    /** @var NodeRenderer<DocumentNode> */
    private NodeRenderer $renderer;

    private CommandBus $commandBus;

    /** @param NodeRenderer<DocumentNode> $renderer */
    public function __construct(
        NodeRenderer $renderer,
        CommandBus $commandBus
    ) {
        $this->renderer = $renderer;
        $this->commandBus = $commandBus;
    }

    public function supports(string $outputFormat): bool
    {
        return $outputFormat === self::TYPE;
    }

    public function render(RenderCommand $renderCommand): void
    {
        foreach ($renderCommand->getDocuments() as $document) {
            $this->commandBus->handle(
                new RenderDocumentCommand(
                    $document,
                    RenderContext::forDocument(
                        $document,
                        $renderCommand->getOrigin(),
                        $renderCommand->getDestination(),
                        $renderCommand->getDestinationPath(),
                        $renderCommand->getMetas(),
                        new UrlGenerator(),
                        $renderCommand->getOutputFormat()
                    )
                )
            );
        }
    }
}

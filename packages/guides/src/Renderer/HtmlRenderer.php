<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Renderer;

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

    /** @param NodeRenderer<DocumentNode> $renderer */
    public function __construct(private NodeRenderer $renderer)
    {
    }

    public function supports(string $outputFormat): bool
    {
        return $outputFormat === self::TYPE;
    }

    public function render(RenderCommand $renderCommand): void
    {
        $renderDocumentHandler = new RenderDocumentHandler($this->renderer);
        foreach ($renderCommand->getDocuments() as $document) {
            $renderDocumentHandler->handle(
                new RenderDocumentCommand(
                    $document,
                    RenderContext::forDocument(
                        $document,
                        $renderCommand->getOrigin(),
                        $renderCommand->getDestination(),
                        $renderCommand->getDestinationPath(),
                        $renderCommand->getMetas(),
                        new UrlGenerator(),
                        $renderCommand->getOutputFormat(),
                    ),
                ),
            );
        }
    }
}

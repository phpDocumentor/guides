<?php

namespace phpDocumentor\Guides\Renderer;

use phpDocumentor\Guides\Handlers\RenderCommand;
use phpDocumentor\Guides\Handlers\RenderDocumentCommand;
use phpDocumentor\Guides\Handlers\RenderDocumentHandler;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Setup\QuickStart;
use phpDocumentor\Guides\UrlGenerator;

class LatexTypeRenderer implements TypeRenderer
{
    public const TYPE = 'tex';

    public function supports(string $outputFormat): bool
    {
        return $outputFormat === self::TYPE;
    }

    public function render(RenderCommand $renderCommand): void
    {
        $renderer = QuickStart::createRenderer($renderCommand->getMetas());
        $renderDocumentHandler = new RenderDocumentHandler($renderer);
        foreach ($renderCommand->getDocuments() as $document) {
            $renderDocumentHandler->handle(
                new RenderDocumentCommand(
                    $document,
                    RenderContext::forDocument(
                        $document,
                        $renderCommand->getOrigin(),
                        $renderCommand->getDestination(),
                        '/',
                        $renderCommand->getMetas(),
                        new UrlGenerator(),
                        self::TYPE
                    )
                )
            );
        }
    }
}

<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Renderer;

use League\Tactician\CommandBus;
use phpDocumentor\Guides\Handlers\RenderCommand;
use phpDocumentor\Guides\Handlers\RenderDocumentCommand;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\UrlGenerator;

abstract class BaseTypeRenderer implements TypeRenderer
{
    public function __construct(
        protected readonly CommandBus $commandBus,
    ) {
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
                        $renderCommand->getOutputFormat(),
                    ),
                ),
            );
        }
    }
}

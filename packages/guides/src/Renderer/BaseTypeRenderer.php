<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Renderer;

use League\Tactician\CommandBus;
use phpDocumentor\Guides\Handlers\RenderCommand;
use phpDocumentor\Guides\Handlers\RenderDocumentCommand;
use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolverInterface;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\UrlGenerator\UrlGeneratorInterface;

abstract class BaseTypeRenderer implements TypeRenderer
{
    public function __construct(
        protected readonly CommandBus $commandBus,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly DocumentNameResolverInterface $documentNameResolver,
    ) {
    }

    public function render(RenderCommand $renderCommand): void
    {
        foreach ($renderCommand->getDocumentIterator() as $document) {
            $this->commandBus->handle(
                new RenderDocumentCommand(
                    $document,
                    RenderContext::forDocument(
                        $document,
                        $renderCommand->getDocumentArray(),
                        $renderCommand->getOrigin(),
                        $renderCommand->getDestination(),
                        $renderCommand->getDestinationPath(),
                        $this->urlGenerator,
                        $this->documentNameResolver,
                        $renderCommand->getOutputFormat(),
                        $renderCommand->getProjectNode(),
                    ),
                ),
            );
        }
    }
}

<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Renderer;

use phpDocumentor\Guides\Handlers\RenderCommand;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\TemplateRenderer;

class LatexRenderer implements TypeRenderer
{
    public function __construct(private readonly TemplateRenderer $renderer)
    {
    }

    public function render(RenderCommand $renderCommand): void
    {
        $projectNode = $renderCommand->getProjectNode();

        $context = RenderContext::forProject(
            $projectNode,
            $renderCommand->getDocumentArray(),
            $renderCommand->getOrigin(),
            $renderCommand->getDestination(),
            $renderCommand->getDestinationPath(),
            'tex',
        )->withIterator($renderCommand->getDocumentIterator());

        $context->getDestination()->put(
            $renderCommand->getDestinationPath() . '/index.tex',
            $this->renderer->renderTemplate(
                $context,
                'structure/project.tex.twig',
                [
                    'project' => $projectNode,
                    'documents' => $context->getIterator(),
                ],
            ),
        );
    }
}

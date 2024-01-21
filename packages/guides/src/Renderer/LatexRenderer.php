<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Guides\Renderer;

use phpDocumentor\Guides\Handlers\RenderCommand;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\TemplateRenderer;

final class LatexRenderer implements TypeRenderer
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

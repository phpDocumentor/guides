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

use League\Tactician\CommandBus;
use phpDocumentor\Guides\Handlers\RenderCommand;
use phpDocumentor\Guides\Handlers\RenderDocumentCommand;
use phpDocumentor\Guides\RenderContext;

abstract class BaseTypeRenderer implements TypeRenderer
{
    public function __construct(protected readonly CommandBus $commandBus)
    {
    }

    public function render(RenderCommand $renderCommand): void
    {
        $context = RenderContext::forProject(
            $renderCommand->getProjectNode(),
            $renderCommand->getDocumentArray(),
            $renderCommand->getOrigin(),
            $renderCommand->getDestination(),
            $renderCommand->getDestinationPath(),
            $renderCommand->getOutputFormat(),
        )->withIterator($renderCommand->getDocumentIterator());

        foreach ($context->getIterator() as $document) {
            $this->commandBus->handle(
                new RenderDocumentCommand(
                    $document,
                    $context->withDocument($document),
                ),
            );
        }
    }
}

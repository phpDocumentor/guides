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

namespace phpDocumentor\Guides\Handlers;

use phpDocumentor\Guides\Event\PostRenderDocument;
use phpDocumentor\Guides\Event\PreRenderDocument;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\DocumentNode;
use Psr\EventDispatcher\EventDispatcherInterface;

use function assert;

final class RenderDocumentHandler
{
    /** @param NodeRenderer<DocumentNode> $renderer */
    public function __construct(
        private readonly NodeRenderer $renderer,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function handle(RenderDocumentCommand $command): void
    {
        $preRenderDocumentEvent = $this->eventDispatcher->dispatch(
            new PreRenderDocument($this->renderer, $command),
        );
        assert($preRenderDocumentEvent instanceof PreRenderDocument);

        $command->getContext()->getDestination()->put(
            $command->getFileDestination(),
            $this->renderer->render(
                $command->getDocument(),
                $command->getContext(),
            ),
        );

        $postRenderDocumentEvent = $this->eventDispatcher->dispatch(
            new PostRenderDocument($this->renderer, $command),
        );
        assert($postRenderDocumentEvent instanceof PostRenderDocument);
    }
}

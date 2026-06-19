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

namespace phpDocumentor\Guides\Pages\EventListener;

use phpDocumentor\Guides\Event\PostRenderProcess;
use phpDocumentor\Guides\NodeRenderers\DelegatingNodeRenderer;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Pages\Nodes\RenderablePageInterface;
use phpDocumentor\Guides\Pages\PagesRegistry;
use phpDocumentor\Guides\RenderContext;
use Psr\Log\LoggerInterface;

use function ltrim;
use function sprintf;

/**
 * Renders all standalone pages and content-type items — including overview
 * pages — after the main documentation has been rendered.
 *
 * Triggered by {@see PostRenderProcess}, this listener:
 *
 * 1. Guards against non-HTML output formats.
 * 2. Iterates over **all renderables** ({@see PagesRegistry::getAllRenderables()})
 *    and delegates each one to the format-specific
 *    {@see DelegatingNodeRenderer} (`page` format), which in turn dispatches to
 *    {@see \phpDocumentor\Guides\Pages\NodeRenderers\Html\PageNodeRenderer}.
 *    This includes {@see \phpDocumentor\Guides\Pages\Nodes\ContentTypeOverviewNode}
 *    instances created during the parse phase by
 *    {@see \phpDocumentor\Guides\Pages\EventListener\ParseContentTypeListener}.
 *    Template resolution is entirely node-driven — no template logic lives here.
 * 3. Writes each rendered HTML file to the destination filesystem.
 */
final class RenderPagesListener
{
    public function __construct(
        private readonly PagesRegistry $registry,
        private readonly DelegatingNodeRenderer $delegatingRenderer,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(PostRenderProcess $event): void
    {
        $command = $event->getCommand();

        if ($command->getOutputFormat() !== 'html') {
            return;
        }

        $origin        = $command->getOrigin();
        $destination   = $command->getDestination();
        $projectNode   = $command->getProjectNode();
        $documentArray = $command->getDocumentArray();

        // Render all pages, content-type items, and overview pages
        foreach ($this->registry->getAllRenderables() as $renderable) {
            $context = $this->buildContext($renderable, $origin, $destination, $projectNode, $documentArray);

            $html = $this->delegatingRenderer->render($renderable, $context);

            $outputPath = ltrim($renderable->getOutputPath() . '.html', '/');
            $destination->put($outputPath, $html);

            $this->logger->info(sprintf('[guides-pages] Rendered page to "%s"', $outputPath));
        }
    }

    /** @param array<string, DocumentNode> $documentArray */
    private function buildContext(
        RenderablePageInterface $renderable,
        mixed $origin,
        mixed $destination,
        mixed $projectNode,
        array $documentArray,
    ): RenderContext {
        $tempDoc = new DocumentNode($renderable->getFilePath(), $renderable->getFilePath());

        return RenderContext::forDocument(
            $tempDoc,
            $documentArray,
            $origin,
            $destination,
            '/',
            'html',
            $projectNode,
        )->withOutputFilePath($renderable->getOutputPath() . '.html');
    }
}

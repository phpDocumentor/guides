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
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Pages\PagesRegistry;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\TemplateRenderer;
use Psr\Log\LoggerInterface;

use function ltrim;
use function sprintf;

/**
 * Renders compiled standalone pages after the main documentation has been rendered.
 *
 * Triggered by {@see PostRenderProcess}, this listener:
 *
 * 1. Guards against non-HTML output formats and returns early if the format is not HTML.
 * 2. Iterates over all {@see PageNode}s stored in the {@see PagesRegistry} by
 *    {@see ParsePagesListener}.
 * 3. Renders each page using the "structure/page.html.twig" template via the
 *    shared {@see TemplateRenderer}.
 * 4. Writes the rendered HTML to the destination filesystem provided by the
 *    {@see \phpDocumentor\Guides\Handlers\RenderCommand}.
 */
final class RenderPagesListener
{
    public function __construct(
        private readonly PagesRegistry $registry,
        private readonly TemplateRenderer $templateRenderer,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(PostRenderProcess $event): void
    {
        $command = $event->getCommand();

        if ($command->getOutputFormat() !== 'html') {
            return;
        }

        $origin      = $command->getOrigin();
        $destination = $command->getDestination();
        $projectNode = $command->getProjectNode();
        $documentArray = $command->getDocumentArray();


        foreach ($this->registry->getPages() as $pageNode) {
            // Build a thin DocumentNode purely for RenderContext compatibility
            $tempDoc = new DocumentNode($pageNode->getFilePath(), $pageNode->getFilePath());

            $context = RenderContext::forDocument(
                $tempDoc,
                $documentArray,
                $origin,
                $destination,
                '/',
                'html',
                $projectNode,
            )->withOutputFilePath($pageNode->getOutputPath() . '.html');

            $html = $this->templateRenderer->renderTemplate(
                $context,
                'structure/page.html.twig',
                [
                    'node'  => $pageNode,
                    'title' => $pageNode->getPageTitle(),
                ],
            );

            $outputPath = ltrim($pageNode->getOutputPath() . '.html', '/');
            $destination->put($outputPath, $html);

            $this->logger->info(sprintf('[guides-pages] Rendered page to "%s"', $outputPath));
        }
    }
}

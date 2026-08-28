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

use League\Tactician\CommandBus;
use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Event\PostParseProcess;
use phpDocumentor\Guides\FileCollector;
use phpDocumentor\Guides\Handlers\CompileDocumentsCommand;
use phpDocumentor\Guides\Handlers\ParseFileCommand;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Pages\Collection;
use phpDocumentor\Guides\Pages\Nodes\ContentTypeItemNode;
use phpDocumentor\Guides\Pages\Nodes\ContentTypeOverviewNode;
use phpDocumentor\Guides\Pages\PagesRegistry;
use Psr\Log\LoggerInterface;

use function array_values;
use function assert;
use function sprintf;

/**
 * Parses and compiles content-type collection items after the main documentation
 * has been parsed.
 *
 * Triggered by {@see PostParseProcess}, this listener iterates over every
 * collection defined in `guides.xml` and for each one:
 *
 * 1. Checks whether the collection's `source_directory` exists in the origin
 *    filesystem; logs a debug message and skips when it does not.
 * 2. Discovers all source files via {@see FileCollector}.
 * 3. Parses each file via {@see ParseFileCommand} → {@see DocumentNode}.
 * 4. Promotes each {@see DocumentNode} to a {@see ContentTypeItemNode} via
 *    {@see ContentTypeItemNode::from()}, extracting date, template override,
 *    and output-path metadata.
 * 5. Stores each item in the {@see PagesRegistry} under the collection key.
 *
 * After **all** collections have been parsed, a single
 * {@see CompileDocumentsCommand} is dispatched for all items together so that
 * cross-references between items (and between items and the main documentation
 * tree) are resolved correctly.
 */
final class ParseContentTypeListener
{
    /** @param Collection[] $collections */
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly FileCollector $fileCollector,
        private readonly PagesRegistry $registry,
        private readonly LoggerInterface $logger,
        private readonly array $collections,
    ) {
    }

    public function __invoke(PostParseProcess $event): void
    {
        if ($this->collections === []) {
            return;
        }

        $parseDirectoryCommand = $event->getParseDirectoryCommand();
        $origin      = $parseDirectoryCommand->getOrigin();
        $inputFormat = $parseDirectoryCommand->getInputFormat();
        $projectNode = $parseDirectoryCommand->getProjectNode();

        /** @var array<string, DocumentNode> $tempDocuments keyed by filePath across all collections */
        $tempDocuments = [];

        foreach ($this->collections as $collection) {
            $sourceDir = $collection->getSourceDirectory();

            if (!$origin->has($sourceDir) && !$origin->has($sourceDir . '/')) {
                $this->logger->debug(sprintf(
                    '[guides-pages] Content-type source directory "%s" not found; skipping.',
                    $sourceDir,
                ));
                continue;
            }

            $files = $this->fileCollector->collect($origin, $sourceDir, $inputFormat);

            if ($files->count() === 0) {
                continue;
            }

            foreach ($files as $file) {
                $this->logger->info(sprintf(
                    '[guides-pages] Parsing content-type item "%s" (collection: %s)',
                    $file,
                    $sourceDir,
                ));

                $documentNode = $this->commandBus->handle(
                    new ParseFileCommand(
                        $origin,
                        $sourceDir,
                        $file,
                        $inputFormat,
                        1,
                        $projectNode,
                        false,
                    ),
                );
                assert($documentNode instanceof DocumentNode || $documentNode === null);

                if ($documentNode === null) {
                    continue;
                }

                $documentNode = $documentNode->withIsRoot(true)->setOrphan(true);
                $item = ContentTypeItemNode::from($documentNode)->withSourceDirectory($sourceDir);

                // Stamp the collection-level default template onto items that do
                // not carry their own per-item `:page-template:` override.  This
                // moves template resolution from the render listener into the node
                // itself, so the NodeRenderer can read it via getItemTemplate().
                if ($item->getItemTemplate() === null) {
                    $item = $item->withItemTemplate($collection->getItemTemplate());
                }

                $this->registry->addCollectionItem($sourceDir, $item);
                $tempDocuments[$item->getFilePath()] = $item->toDocument();
            }
        }

        foreach ($this->collections as $collection) {
            $sourceDir = $collection->getSourceDirectory();
            $this->registry->addOverview(new ContentTypeOverviewNode(
                $collection->getOverviewPath(),
                $collection->getOverviewTitle(),
                $collection->getOverviewTemplate(),
                $this->registry->getSortedCollectionItems($sourceDir),
            ));
        }

        if ($tempDocuments === []) {
            return;
        }

        $compilerContext = new CompilerContext($projectNode);

        /** @var DocumentNode[] $compiledDocs */
        $compiledDocs = $this->commandBus->handle(
            new CompileDocumentsCommand(
                array_values($tempDocuments),
                $compilerContext,
            ),
        );

        // Re-distribute compiled documents back to their respective collections
        foreach ($compiledDocs as $compiledDoc) {
            foreach ($this->collections as $collection) {
                $sourceDir = $collection->getSourceDirectory();
                $items     = $this->registry->getCollectionItems($sourceDir);

                foreach ($items as $item) {
                    if ($item->getFilePath() === $compiledDoc->getFilePath()) {
                        $this->registry->updateCollectionItems($sourceDir, [$compiledDoc]);
                        break;
                    }
                }
            }
        }
    }
}

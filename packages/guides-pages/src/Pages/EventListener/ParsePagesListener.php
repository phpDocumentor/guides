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
use phpDocumentor\Guides\Pages\Nodes\PageNode;
use phpDocumentor\Guides\Pages\PagesRegistry;
use Psr\Log\LoggerInterface;

use function array_values;
use function assert;
use function ltrim;
use function sprintf;

/**
 * Parses and compiles standalone pages after the main documentation has been parsed.
 *
 * Triggered by {@see PostParseProcess}, this listener:
 *
 * 1. Discovers all source files in the configured pages source directory.
 * 2. Parses each file via {@see ParseFileCommand} → {@see DocumentNode}.
 * 3. Promotes each {@see DocumentNode} to a {@see PageNode}, extracting any
 *    {@see PageDestinationNode} to override the default output path.
 * 4. Wraps each {@see PageNode}'s body children in a temporary {@see DocumentNode}
 *    and runs the standard compiler via {@see CompileDocumentsCommand} against the
 *    shared {@see \phpDocumentor\Guides\Nodes\ProjectNode}, so that cross-references
 *    inside pages are resolved correctly.
 * 5. Stores each compiled {@see PageNode} in the {@see PagesRegistry} for later
 *    rendering by {@see RenderPagesListener}.
 */
final class ParsePagesListener
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly FileCollector $fileCollector,
        private readonly PagesRegistry $registry,
        private readonly LoggerInterface $logger,
        private readonly string $sourceDirectory,
    ) {
    }

    public function __invoke(PostParseProcess $event): void
    {
        $parseDirectoryCommand = $event->getParseDirectoryCommand();
        $origin      = $parseDirectoryCommand->getOrigin();
        $inputFormat = $parseDirectoryCommand->getInputFormat();
        $projectNode = $parseDirectoryCommand->getProjectNode();
        $sourceDir   = ltrim($this->sourceDirectory, '/');

        if (!$origin->has($sourceDir) && !$origin->has($sourceDir . '/')) {
            $this->logger->debug(sprintf(
                '[guides-pages] Source directory "%s" not found; skipping pages pipeline.',
                $sourceDir,
            ));

            return;
        }

        $files = $this->fileCollector->collect($origin, $sourceDir, $inputFormat);

        if ($files->count() === 0) {
            return;
        }

        foreach ($files as $file) {
            $this->logger->info(sprintf('[guides-pages] Parsing page "%s"', $file));

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

            $this->registry->addPage(PageNode::from($documentNode));
        }

        $compilerContext = new CompilerContext($projectNode);

        /** @var array<string, DocumentNode> $tempDocuments */
        $tempDocuments = [];
        foreach ($this->registry->getPages() as $file => $pageNode) {
            $tempDocuments[$file] = $pageNode->toDocument();
        }

        /** @var DocumentNode[] $compiledDocs */
        $compiledDocs = $this->commandBus->handle(
            new CompileDocumentsCommand(
                array_values($tempDocuments),
                $compilerContext,
            ),
        );

        $this->registry->updatePages($compiledDocs);
    }
}

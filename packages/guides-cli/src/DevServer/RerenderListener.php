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

namespace phpDocumentor\Guides\Cli\DevServer;

use League\Tactician\CommandBus;
use phpDocumentor\DevServer\Server;
use phpDocumentor\DevServer\Watcher\FileModifiedEvent;
use phpDocumentor\FileSystem\FileSystem;
use phpDocumentor\FileSystem\FlySystemAdapter;
use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Handlers\CompileDocumentsCommand;
use phpDocumentor\Guides\Handlers\ParseFileCommand;
use phpDocumentor\Guides\Handlers\RenderDocumentCommand;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\DocumentListIterator;
use phpDocumentor\Guides\Settings\ProjectSettings;
use Symfony\Component\Console\Output\OutputInterface;

use function array_find_key;
use function assert;
use function current;
use function sprintf;
use function substr;

final class RerenderListener
{
    /** @param array<string, DocumentNode> $documents */
    public function __construct(
        private readonly OutputInterface $output,
        private readonly CommandBus $commandBus,
        private readonly FileSystem $sourceFileSystem,
        private readonly ProjectSettings $settings,
        private readonly ProjectNode $projectNode,
        private array $documents,
        private readonly Server $server,
    ) {
    }

    public function __invoke(FileModifiedEvent $event): void
    {
        $this->output->writeln(
            sprintf(
                'File modified: %s, rerendering...',
                $event->path,
            ),
        );
        $file = substr($event->path, 0, -4);

        $document = $this->commandBus->handle(
            new ParseFileCommand(
                $this->sourceFileSystem,
                '',
                $file,
                $this->settings->getInputFormat(),
                1,
                $this->projectNode,
                true,
            ),
        );
        assert($document instanceof DocumentNode);

        /** @var array<string, DocumentNode> $documents */
        $documents = $this->commandBus->handle(new CompileDocumentsCommand([$file => $document], new CompilerContext($this->projectNode)));
        $key = array_find_key($this->documents, static fn (DocumentNode $entry) => $entry->getFilePath() === $document->getFilePath());
        $this->documents[$key] = current($documents);
        $destinationFileSystem = FlySystemAdapter::createForPath($this->settings->getOutput());

        $documentIterator = DocumentListIterator::create(
            $this->projectNode->getRootDocumentEntry(),
            $this->documents,
        );

        $renderContext = RenderContext::forProject(
            $this->projectNode,
            $this->documents,
            $this->sourceFileSystem,
            $destinationFileSystem,
            '/',
            'html',
        )->withIterator($documentIterator);

        $this->commandBus->handle(
            new RenderDocumentCommand(
                $this->documents[$file],
                $renderContext->withDocument($this->documents[$file]),
            ),
        );

        $this->output->writeln('Rerendering completed.');
        $this->server->notifyClients();
    }
}

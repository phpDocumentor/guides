<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Handlers;

use InvalidArgumentException;
use League\Flysystem\FilesystemInterface;
use League\Tactician\CommandBus;
use phpDocumentor\Guides\FileCollector;
use phpDocumentor\Guides\Nodes\DocumentNode;

use function sprintf;

final class ParseDirectoryHandler
{
    private const INDEX_FILE_NAMES = ['index', 'Index'];

    public function __construct(
        private readonly FileCollector $fileCollector,
        private readonly CommandBus $commandBus,
    ) {
    }

    /** @return DocumentNode[] */
    public function handle(ParseDirectoryCommand $command): array
    {
        $origin = $command->getOrigin();
        $currentDirectory = $command->getDirectory();
        $extension = $command->getInputFormat();

        $indexFileName = $this->getIndexFile(
            $origin,
            $currentDirectory,
            $extension,
        );

        $files = $this->fileCollector->collect($origin, $currentDirectory, $extension);
        $documents = [];
        // handle index file first, so that the document tree is build from there
        $rootDocument = $this->commandBus->handle(
            new ParseFileCommand($origin, $currentDirectory, $indexFileName, $extension, 1, $command->getProjectNode()),
        );
        $command->getProjectNode()->setRootDocumentNode($rootDocument);
        $documents[] = $rootDocument;
        foreach ($files as $file) {
            if ($file === $indexFileName) {
                continue;
            }

            $documents[] = $this->commandBus->handle(
                new ParseFileCommand($origin, $currentDirectory, $file, $extension, 1, $command->getProjectNode()),
            );
        }

        return $documents;
    }

    private function getIndexFile(
        FilesystemInterface $filesystem,
        string $directory,
        string $sourceFormat,
    ): string {
        $extension = $sourceFormat;
        foreach (self::INDEX_FILE_NAMES as $indexName) {
            $indexFilename = sprintf('%s.%s', $indexName, $extension);
            if ($filesystem->has($directory . '/' . $indexFilename)) {
                return $indexName;
            }
        }

        $indexFilename = sprintf('%s.%s', self::INDEX_FILE_NAMES[0], $extension);

        throw new InvalidArgumentException(
            sprintf('Could not find index file "%s" in "%s"', $indexFilename, $directory),
        );
    }
}

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

        $indexName = $this->getDirectoryIndexFile(
            $origin,
            $currentDirectory,
            $extension,
        );

        $files = $this->fileCollector->collect($origin, $currentDirectory, $extension);
        $documents = [];
        foreach ($files as $file) {
            $documents[] = $this->commandBus->handle(
                new ParseFileCommand($origin, $currentDirectory, $file, $extension, 1, $command->getProjectNode(), $indexName === $file),
            );
        }

        return $documents;
    }

    private function getDirectoryIndexFile(
        FilesystemInterface $filesystem,
        string $directory,
        string $sourceFormat,
    ): string {
        $extension = $sourceFormat;

        // On macOS filesystems, a file-check against "index.rst"
        // using $filesystem->has() would return TRUE, when in fact
        // a file might be stored as "Index.rst". Thus, at this point
        // we fetch the whole directory list and compare the contents
        // with if the INDEX_FILE_NAMES entry matches. This ensures
        // that we get the file with exactly the casing that is returned
        // from the filesystem.
        $contentFromFilesystem = $filesystem->listContents($directory);
        $hashedContentFromFilesystem = [];
        foreach($contentFromFilesystem AS $itemFromFilesystem) {
            $hashedContentFromFilesystem[$itemFromFilesystem['basename']] = true;
        }
        foreach (self::INDEX_FILE_NAMES as $indexName) {
            $indexFilename = sprintf('%s.%s', $indexName, $extension);
            if (isset($hashedContentFromFilesystem[$indexFilename])) {
                return $indexName;
            }
        }

        $indexFilename = sprintf('%s.%s', self::INDEX_FILE_NAMES[0], $extension);

        throw new InvalidArgumentException(
            sprintf('Could not find index file "%s" in "%s"', $indexFilename, $directory),
        );
    }
}

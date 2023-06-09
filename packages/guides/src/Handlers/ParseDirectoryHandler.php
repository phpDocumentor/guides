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

        $this->guardThatAnIndexFileExists(
            $origin,
            $currentDirectory,
            $extension,
        );

        $files = $this->fileCollector->collect($origin, $currentDirectory, $extension);
        $documents = [];
        foreach ($files as $file) {
            $documents[] = $this->commandBus->handle(
                new ParseFileCommand($origin, $currentDirectory, $file, $extension, 1, $command->getProjectNode()),
            );
        }

        return $documents;
    }

    private function guardThatAnIndexFileExists(
        FilesystemInterface $filesystem,
        string $directory,
        string $sourceFormat,
    ): void {
        $extension = $sourceFormat;
        $hasIndexFile = false;
        foreach (self::INDEX_FILE_NAMES as $indexName) {
            $indexFilename = sprintf('%s.%s', $indexName, $extension);
            if ($filesystem->has($directory . '/' . $indexFilename)) {
                $hasIndexFile = true;
                break;
            }
        }

        if (!$hasIndexFile) {
            $indexFilename = sprintf('%s.%s', self::INDEX_FILE_NAMES[0], $extension);

            throw new InvalidArgumentException(
                sprintf('Could not find index file "%s" in "%s"', $indexFilename, $directory),
            );
        }
    }
}

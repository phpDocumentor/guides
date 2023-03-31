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
    private FileCollector $fileCollector;
    private CommandBus $commandBus;
    /** @var string[]  */
    private array $indexFileNames = ['index', 'Index'];

    public function __construct(FileCollector $scanner, CommandBus $commandBus)
    {
        $this->fileCollector = $scanner;
        $this->commandBus = $commandBus;
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
            $extension
        );

        $files = $this->fileCollector->collect($origin, $currentDirectory, $extension);
        $documents = [];
        foreach ($files as $file) {
            $documents[] = $this->commandBus->handle(
                new ParseFileCommand($origin, $currentDirectory, $file, $extension, 1)
            );
        }

        return $documents;
    }

    private function guardThatAnIndexFileExists(
        FilesystemInterface $filesystem,
        string $directory,
        string $sourceFormat
    ): void {
        $extension = $sourceFormat;
        $hasIndexFile = false;
        foreach ($this->indexFileNames as $indexName) {
            $indexFilename = sprintf('%s.%s', $indexName, $extension);
            if (!$filesystem->has($directory . '/' . $indexFilename)) {
                $hasIndexFile = true;
                break;
            }
        }

        if (!$hasIndexFile) {
            $indexFilename = sprintf('%s.%s', $this->indexFileNames[0], $extension);

            throw new InvalidArgumentException(
                sprintf('Could not find index file "%s" in "%s"', $indexFilename, $directory)
            );
        }
    }
}

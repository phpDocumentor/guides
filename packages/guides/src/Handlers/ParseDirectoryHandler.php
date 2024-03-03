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

use InvalidArgumentException;
use League\Flysystem\FilesystemInterface;
use League\Tactician\CommandBus;
use phpDocumentor\Guides\Event\PostCollectFilesForParsingEvent;
use phpDocumentor\Guides\Event\PostParseProcess;
use phpDocumentor\Guides\Event\PreParseProcess;
use phpDocumentor\Guides\FileCollector;
use phpDocumentor\Guides\Nodes\DocumentNode;
use Psr\EventDispatcher\EventDispatcherInterface;

use function assert;
use function sprintf;

final class ParseDirectoryHandler
{
    private const INDEX_FILE_NAMES = ['index', 'Index'];

    public function __construct(
        private readonly FileCollector $fileCollector,
        private readonly CommandBus $commandBus,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /** @return DocumentNode[] */
    public function handle(ParseDirectoryCommand $command): array
    {
        $preParseProcessEvent = $this->eventDispatcher->dispatch(
            new PreParseProcess($command),
        );
        assert($preParseProcessEvent instanceof PreParseProcess);
        $command = $preParseProcessEvent->getParseDirectoryCommand();

        $origin = $command->getOrigin();
        $currentDirectory = $command->getDirectory();
        $extension = $command->getInputFormat();

        $indexName = $this->getDirectoryIndexFile(
            $origin,
            $currentDirectory,
            $extension,
        );

        $files = $this->fileCollector->collect($origin, $currentDirectory, $extension, $command->getExcludedSpecification());

        $postCollectFilesForParsingEvent = $this->eventDispatcher->dispatch(
            new PostCollectFilesForParsingEvent($command, $files),
        );
        assert($postCollectFilesForParsingEvent instanceof PostCollectFilesForParsingEvent);
        /** @var DocumentNode[] $documents */
        $documents = [];
        foreach ($postCollectFilesForParsingEvent->getFiles() as $file) {
            $documents[] = $this->commandBus->handle(
                new ParseFileCommand($origin, $currentDirectory, $file, $extension, 1, $command->getProjectNode(), $indexName === $file),
            );
        }

        $postCollectFilesForParsingEvent = $this->eventDispatcher->dispatch(
            new PostParseProcess($command, $documents),
        );
        assert($postCollectFilesForParsingEvent instanceof PostParseProcess);

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
        foreach ($contentFromFilesystem as $itemFromFilesystem) {
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

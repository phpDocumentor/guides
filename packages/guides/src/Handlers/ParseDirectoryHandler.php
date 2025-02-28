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
use phpDocumentor\FileSystem\FileSystem;
use phpDocumentor\Guides\Event\PostCollectFilesForParsingEvent;
use phpDocumentor\Guides\Event\PostParseProcess;
use phpDocumentor\Guides\Event\PreParseProcess;
use phpDocumentor\Guides\FileCollector;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Settings\ProjectSettings;
use phpDocumentor\Guides\Settings\SettingsManager;
use Psr\EventDispatcher\EventDispatcherInterface;

use function array_map;
use function assert;
use function explode;
use function implode;
use function sprintf;

final class ParseDirectoryHandler
{
    private SettingsManager $settingsManager;

    public function __construct(
        private readonly FileCollector $fileCollector,
        private readonly CommandBus $commandBus,
        private readonly EventDispatcherInterface $eventDispatcher,
        SettingsManager|null $settingsManager = null,
    ) {
        // if for backward compatibility reasons no settings manager was passed, use the defaults
        $this->settingsManager = $settingsManager ?? new SettingsManager(new ProjectSettings());
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

        $files = $this->fileCollector->collect(
            $origin,
            $currentDirectory,
            $extension,
            $command->hasExclude() ? $command->getExclude() : $command->getExcludedSpecification(),
        );

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
        FilesystemInterface|FileSystem $filesystem,
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

        $indexFileNames = array_map('trim', explode(',', $this->settingsManager->getProjectSettings()->getIndexName()));

        $indexNamesNotFound = [];
        foreach ($indexFileNames as $indexName) {
            $fullIndexFilename = sprintf('%s.%s', $indexName, $extension);
            if (isset($hashedContentFromFilesystem[$fullIndexFilename])) {
                return $indexName;
            }

            $indexNamesNotFound[] = $fullIndexFilename;
        }

        throw new InvalidArgumentException(
            sprintf('Could not find an index file "%s", expected file names: %s', $directory, implode(', ', $indexNamesNotFound)),
        );
    }
}

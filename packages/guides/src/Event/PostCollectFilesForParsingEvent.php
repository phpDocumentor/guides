<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Event;

use phpDocumentor\Guides\Files;
use phpDocumentor\Guides\Handlers\ParseDirectoryCommand;

/**
 * This event is called after all files have been collected for parsing
 * But before the actual parsing begins.
 *
 * It can be used to manipulate the files to be parsed.
 */
final class PostCollectFilesForParsingEvent
{
    public function __construct(
        private readonly ParseDirectoryCommand $command,
        private Files $files,
    ) {
    }

    public function getCommand(): ParseDirectoryCommand
    {
        return $this->command;
    }

    public function getFiles(): Files
    {
        return $this->files;
    }

    public function setFiles(Files $files): void
    {
        $this->files = $files;
    }
}

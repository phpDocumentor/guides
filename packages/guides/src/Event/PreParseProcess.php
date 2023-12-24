<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Event;

use phpDocumentor\Guides\Handlers\ParseDirectoryCommand;

/**
 * This event is dispatched right before the overall parsing process is
 * started.
 *
 * It can be used to modify the ParseDirectoryCommand, so it could be used to alter the
 * directory to be parsed or the file system to be used.
 */
final class PreParseProcess
{
    public function __construct(
        private ParseDirectoryCommand $parseDirectoryCommand,
    ) {
    }

    public function getParseDirectoryCommand(): ParseDirectoryCommand
    {
        return $this->parseDirectoryCommand;
    }

    public function setParseDirectoryCommand(ParseDirectoryCommand $parseDirectoryCommand): PreParseProcess
    {
        $this->parseDirectoryCommand = $parseDirectoryCommand;

        return $this;
    }
}

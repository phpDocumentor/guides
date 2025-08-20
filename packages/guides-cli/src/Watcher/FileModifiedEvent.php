<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Cli\Watcher;

final class FileModifiedEvent
{
    public function __construct(public readonly string $path)
    {
    }
}

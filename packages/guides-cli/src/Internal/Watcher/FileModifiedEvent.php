<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Cli\Internal\Watcher;

final class FileModifiedEvent
{
    public function __construct(public readonly string $path)
    {
    }
}

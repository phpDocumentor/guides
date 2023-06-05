<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Handlers;

use League\Flysystem\FilesystemInterface;
use phpDocumentor\Guides\Nodes\ProjectNode;

final class ParseDirectoryCommand
{
    public function __construct(
        private readonly FilesystemInterface $origin,
        private readonly string $directory,
        private readonly string $inputFormat,
        private readonly ProjectNode $projectNode,
    ) {
    }

    public function getOrigin(): FilesystemInterface
    {
        return $this->origin;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function getInputFormat(): string
    {
        return $this->inputFormat;
    }

    public function getProjectNode(): ProjectNode
    {
        return $this->projectNode;
    }
}

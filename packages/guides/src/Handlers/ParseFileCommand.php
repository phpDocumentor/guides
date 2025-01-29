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

use League\Flysystem\FilesystemInterface;
use phpDocumentor\FileSystem\FileSystem;
use phpDocumentor\Guides\Nodes\ProjectNode;

final class ParseFileCommand
{
    public function __construct(
        private readonly FilesystemInterface|FileSystem $origin,
        private readonly string $directory,
        private readonly string $file,
        private readonly string $extension,
        private readonly int $initialHeaderLevel,
        private readonly ProjectNode $projectNode,
        private readonly bool $isRoot,
    ) {
    }

    public function getOrigin(): FilesystemInterface|FileSystem
    {
        return $this->origin;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function getInitialHeaderLevel(): int
    {
        return $this->initialHeaderLevel;
    }

    public function getProjectNode(): ProjectNode
    {
        return $this->projectNode;
    }

    public function isRoot(): bool
    {
        return $this->isRoot;
    }
}

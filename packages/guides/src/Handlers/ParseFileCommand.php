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

final class ParseFileCommand
{
    public function __construct(
        private FilesystemInterface $origin,
        private string $directory,
        private string $file,
        private string $extension,
        private int $initialHeaderLevel,
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
}

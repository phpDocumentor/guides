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

use Flyfinder\Specification\SpecificationInterface;
use League\Flysystem\FilesystemInterface;
use phpDocumentor\FileSystem\FileSystem;
use phpDocumentor\Guides\Nodes\ProjectNode;

final class ParseDirectoryCommand
{
    public function __construct(
        private readonly FilesystemInterface|FileSystem $origin,
        private readonly string $directory,
        private readonly string $inputFormat,
        private readonly ProjectNode $projectNode,
        private readonly SpecificationInterface|null $excludedSpecification = null,
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

    public function getInputFormat(): string
    {
        return $this->inputFormat;
    }

    public function getProjectNode(): ProjectNode
    {
        return $this->projectNode;
    }

    public function getExcludedSpecification(): SpecificationInterface|null
    {
        return $this->excludedSpecification;
    }
}

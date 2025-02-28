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

use Doctrine\Deprecations\Deprecation;
use Flyfinder\Specification\SpecificationInterface;
use League\Flysystem\FilesystemInterface;
use phpDocumentor\FileSystem\FileSystem;
use phpDocumentor\FileSystem\Finder\Exclude;
use phpDocumentor\Guides\Nodes\ProjectNode;

final class ParseDirectoryCommand
{
    private readonly SpecificationInterface|null $excludedSpecification;
    private readonly Exclude|null $exclude;

    public function __construct(
        private readonly FilesystemInterface|FileSystem $origin,
        private readonly string $directory,
        private readonly string $inputFormat,
        private readonly ProjectNode $projectNode,
        SpecificationInterface|Exclude|null $excludedSpecification = null,
    ) {
        if ($excludedSpecification instanceof SpecificationInterface) {
            Deprecation::trigger(
                'phpDocumentor/guides',
                'https://github.com/phpDocumentor/guides/issues/1209',
                'Passing ' . $excludedSpecification::class . ' to ' . self::class . 'will be deprecated,'
                . 'use phpDocumentor\FileSystem\Finder\Exclude instead.',
            );
            $this->excludedSpecification = $excludedSpecification;
            $this->exclude = null;
        } else {
            $this->exclude = $excludedSpecification;
            $this->excludedSpecification = null;
        }
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

    /** @deprecated Specification definition on parse directory is deprecated. Use @{see self::getExclude()} instead.*/
    public function getExcludedSpecification(): SpecificationInterface|null
    {
        Deprecation::trigger(
            'phpDocumentor/guides',
            'https://github.com/phpDocumentor/guides/issues/1209',
            'Specification definition on parse directory is deprecated. Use getExclude() instead.',
        );

        return $this->excludedSpecification ?? null;
    }

    public function getExclude(): Exclude
    {
        return $this->exclude ?? new Exclude();
    }

    public function hasExclude(): bool
    {
        return isset($this->exclude);
    }
}

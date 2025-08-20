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

namespace phpDocumentor\FileSystem\FlysystemV3;

use Flyfinder\Finder;
use Flyfinder\Specification\SpecificationInterface;
use League\Flysystem\Filesystem as LeagueFilesystem;
use League\Flysystem\StorageAttributes;
use phpDocumentor\FileSystem\FileSystem;

final class FlysystemV3 implements FileSystem
{
    private LeagueFilesystem $filesystem;
    private Finder $finder;

    public function __construct(LeagueFilesystem $wrappedFilesystem)
    {
        $this->filesystem = $wrappedFilesystem;
        $this->finder = new Finder($this->filesystem);
    }

    public function has(string $path): bool
    {
        return $this->filesystem->has($path);
    }

    public function isDirectory(string $path): bool
    {
        return $this->filesystem->directoryExists($path);
    }

    public function readStream(string $path): mixed
    {
        return $this->filesystem->readStream($path);
    }

    public function read(string $path): string|false
    {
        return $this->filesystem->read($path);
    }

    public function put(string $path, string $contents): bool
    {
        $this->filesystem->write($path, $contents);

        return true;
    }

    /** @param resource $resource */
    public function putStream(string $path, $resource): void
    {
        $this->filesystem->writeStream($path, $resource);
    }

    /** @return FileAttributes[] */
    public function listContents(string $directory = '', bool $recursive = false): array
    {
        return $this->filesystem->listContents($directory, $recursive)->map(
            static fn (StorageAttributes $attributes) => new FileAttributes($attributes),
        )->toArray();
    }

    /** @return FileAttributes[] */
    public function find(SpecificationInterface $specification): iterable
    {
        foreach ($this->finder->find($specification) as $file) {
            /** @phpstan-ignore-next-line */
            yield new FileAttributes($file);
        }
    }
}

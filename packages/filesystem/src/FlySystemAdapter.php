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

namespace phpDocumentor\FileSystem;

use Flyfinder\Specification\SpecificationInterface;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem as LeagueFilesystem;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\Local\LocalFilesystemAdapter;
use phpDocumentor\FileSystem\FlysystemV1\FlysystemV1;
use phpDocumentor\FileSystem\FlysystemV3\FlysystemV3;

use function class_exists;

class FlySystemAdapter implements FileSystem
{
    public function __construct(
        private FileSystem $filesystem,
    ) {
    }

    public static function createForPath(string $path): self
    {
        if (class_exists(Local::class)) {
            /** @phpstan-ignore-next-line */
            $filesystem = new FlysystemV1(new LeagueFilesystem(new Local($path)));
        } else {
            $filesystem = new FlysystemV3(
                new LeagueFilesystem(
                    new LocalFilesystemAdapter($path),
                ),
            );
        }

        return new self($filesystem);
    }

    public static function createFromFileSystem(LeagueFilesystem|FilesystemInterface $filesystem): self
    {
        if ($filesystem instanceof FilesystemInterface) {
            return new self(new FlysystemV1($filesystem));
        }

        return new self(new FlysystemV3($filesystem));
    }

    public function has(string $path): bool
    {
        return $this->filesystem->has($path);
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
        return $this->filesystem->put($path, $contents);
    }

    /** @param resource $resource */
    public function putStream(string $path, $resource): void
    {
        $this->filesystem->putStream($path, $resource);
    }

    /** @return StorageAttributes[] */
    public function listContents(string $directory = '', bool $recursive = false): array
    {
        return $this->filesystem->listContents($directory, $recursive);
    }

    /** @return StorageAttributes[] */
    public function find(SpecificationInterface $specification): iterable
    {
        return $this->filesystem->find($specification);
    }

    public function isDirectory(string $path): bool
    {
        return $this->filesystem->isDirectory($path);
    }
}

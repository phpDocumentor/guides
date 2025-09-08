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

namespace phpDocumentor\FileSystem\FlysystemV1;

use Flyfinder\Finder;
use Flyfinder\Specification\SpecificationInterface;
use League\Flysystem\FilesystemInterface;
use phpDocumentor\FileSystem\FileSystem;
use phpDocumentor\FileSystem\StorageAttributes;

use function array_map;

class FlysystemV1 implements Filesystem
{
    private FilesystemInterface $filesystem;

    public function __construct(FilesystemInterface $wrappedFilesystem)
    {
        $this->filesystem = $wrappedFilesystem;
        /** @phpstan-ignore-next-line */
        $this->filesystem->addPlugin(new Finder());
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
        return array_map(
            static fn (array $attr) => new \phpDocumentor\FileSystem\FlysystemV1\StorageAttributes($attr),
            $this->filesystem->listContents($directory, $recursive),
        );
    }

    /** @return StorageAttributes[] */
    public function find(SpecificationInterface $specification): iterable
    {
        /** @phpstan-ignore-next-line */
        foreach ($this->filesystem->find($specification) as $file) {
            yield new \phpDocumentor\FileSystem\FlysystemV1\StorageAttributes($file);
        }
    }

    public function isDirectory(string $path): bool
    {
        $metadata = $this->filesystem->getMetadata($path);
        if ($metadata === false) {
            return false;
        }

        return $metadata['type'] === 'dir';
    }
}

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

interface FileSystem
{
    /**
     * Check whether a file exists.
     */
    public function has(string $path): bool;

    /**
     * Retrieves a read-stream for a path.
     *
     * @param string $path The path to the file.
     *
     * @return resource|false The path resource or false on failure.
     *
     * @throws FileNotFoundException
     */
    public function readStream(string $path): mixed;

    /**
     * Read a file.
     *
     * @param string $path The path to the file.
     *
     * @return string|false The file contents or false on failure.
     *
     * @throws FileNotFoundException
     */
    public function read(string $path): string|false;

    public function put(string $path, string $contents): bool;

    /** @param resource $resource */
    public function putStream(string $path, $resource): void;

    /** @return StorageAttributes[] */
    public function listContents(string $directory = '', bool $recursive = false): array;

    /** @return StorageAttributes[] */
    public function find(SpecificationInterface $specification): iterable;

    public function isDirectory(string $path): bool;
}

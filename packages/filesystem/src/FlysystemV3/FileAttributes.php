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

use phpDocumentor\FileSystem\MethodNotAllowedException;
use phpDocumentor\FileSystem\StorageAttributes;

use function basename;
use function dirname;
use function in_array;
use function pathinfo;

final class FileAttributes implements StorageAttributes
{
    public function __construct(
        private readonly \League\Flysystem\StorageAttributes $attributes,
    ) {
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->attributes->offsetExists($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if ($offset === 'basename') {
            return basename($this->attributes->path());
        }

        if ($offset === 'dirname') {
            $dirname = dirname($this->attributes->path());
            if ($dirname === '.') {
                return '';
            }

            return $dirname;
        }

        if (in_array($offset, ['filename'], true)) {
            return pathinfo($this->attributes->path())[$offset];
        }

        return $this->attributes->offsetGet($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new MethodNotAllowedException('Cannot set attributes on storage attributes');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new MethodNotAllowedException('Cannot unset attributes on storage attributes');
    }
}

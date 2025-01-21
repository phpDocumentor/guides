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

use phpDocumentor\FileSystem\MethodNotAllowedException;
use phpDocumentor\FileSystem\StorageAttributes as StorageAttributesInterface;

class StorageAttributes implements StorageAttributesInterface
{
    /** @param string[] $attributes */
    public function __construct(private array $attributes = [])
    {
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->attributes[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new MethodNotAllowedException();
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new MethodNotAllowedException();
    }
}

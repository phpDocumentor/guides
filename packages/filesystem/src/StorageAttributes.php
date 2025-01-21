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

use ArrayAccess;

/** @extends ArrayAccess<string, mixed> */
interface StorageAttributes extends ArrayAccess
{
    /** @return ($offset is 'filename' ? string : mixed)  */
    public function offsetGet(mixed $offset): mixed;
}

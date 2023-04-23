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

final class PersistCacheCommand
{
    public function __construct(private readonly string $cacheDirectory, private readonly bool $useCache = false)
    {
    }

    public function getCacheDirectory(): string
    {
        return $this->cacheDirectory;
    }

    public function useCache(): bool
    {
        return $this->useCache;
    }
}

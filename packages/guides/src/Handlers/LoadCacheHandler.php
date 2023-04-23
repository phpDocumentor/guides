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

use phpDocumentor\Guides\Meta\CachedMetasLoader;
use phpDocumentor\Guides\Metas;

final class LoadCacheHandler
{
    public function __construct(private readonly CachedMetasLoader $cachedMetasLoader, private readonly Metas $metas)
    {
    }

    public function handle(LoadCacheCommand $command): void
    {
        if (!$command->useCaching()) {
            return;
        }

        $this->cachedMetasLoader->loadCachedMetaEntries($command->getCacheDirectory(), $this->metas);
    }
}

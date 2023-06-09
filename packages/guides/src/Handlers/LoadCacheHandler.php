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

final class LoadCacheHandler
{
    public function handle(LoadCacheCommand $command): void
    {
        if (!$command->useCaching()) {
            return;
        }

        // TODO:: Load Cache
    }
}

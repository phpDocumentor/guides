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

namespace phpDocumentor\Guides\ReferenceResolvers\Interlink;

/** @internal This class is not part of the public API of this package and should not be used outside of this package. */
final class ResolvedInventoryLink
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly InventoryLink $link,
    ) {
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getLink(): InventoryLink
    {
        return $this->link;
    }
}

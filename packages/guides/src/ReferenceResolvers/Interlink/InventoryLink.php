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

use phpDocumentor\Guides\ReferenceResolvers\Interlink\Exception\InvalidInventoryLink;

use function preg_match;

final class InventoryLink
{
    public function __construct(
        private readonly string $project,
        private readonly string $version,
        private string $path,
        private readonly string $title,
    ) {
        if (preg_match('/^([a-zA-Z0-9-_.]+\/)*([a-zA-Z0-9-_.]+)(\.html)?(#[^#]*)?$/', $path) < 1) {
            throw new InvalidInventoryLink('Inventory link "' . $path . '" has an invalid scheme. ', 1_671_398_986);
        }
    }

    public function getProject(): string
    {
        return $this->project;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function withPath(string $path): InventoryLink
    {
        $that = clone$this;
        $that->path = $path;

        return $that;
    }
}

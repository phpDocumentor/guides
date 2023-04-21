<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Intersphinx;

use function preg_match;

final class InventoryLink
{
    public function __construct(
        private string $project,
        private string $version,
        private string $path,
        private string $title,
    ) {
        if (preg_match('/^([a-zA-Z0-9-_.]+\/)*([a-zA-Z0-9-_.])+\.html(#[^#]*)?$/', $path) < 1) {
            throw new InvalidInventoryLink('Inventory link "' . $path . '" has an invalid scheme. ', 1671398986);
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
}

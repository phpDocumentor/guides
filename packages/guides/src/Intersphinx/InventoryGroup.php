<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Intersphinx;

use RuntimeException;

use function array_key_exists;
use function strtolower;

final class InventoryGroup
{
    /** @var InventoryLink[]  */
    private array $links = [];

    public function addLink(string $key, InventoryLink $link): void
    {
        $lowerCaseKey               = strtolower($key);
        $this->links[$lowerCaseKey] = $link;
    }

    public function hasLink(string $key): bool
    {
        $lowerCaseKey = strtolower($key);

        return array_key_exists($lowerCaseKey, $this->links);
    }

    public function getLink(string $key): InventoryLink
    {
        $lowerCaseKey = strtolower($key);
        if (!array_key_exists($lowerCaseKey, $this->links)) {
            throw new RuntimeException('Inventory link with key ' . $lowerCaseKey . ' not found. ', 1_671_398_986);
        }

        return $this->links[$lowerCaseKey];
    }
}

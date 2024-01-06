<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Interlink;

use phpDocumentor\Guides\Interlink\Exception\InterlinkNotFound;

interface InventoryRepository
{
    /** @throws InterlinkNotFound */
    public function getLink(string $inventoryKey, string $groupKey, string $linkKey): InventoryLink;

    public function hasInventory(string $key): bool;

    public function getInventory(string $key): Inventory;
}

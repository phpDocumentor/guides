<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Interlink;

interface InventoryRepository
{
    public function hasInventory(string $key): bool;

    public function getInventory(string $key): Inventory;
}

<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Interlink;

interface InventoryLoader
{
    public function loadInventory(Inventory $inventory): void;
}

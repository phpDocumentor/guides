<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers\Interlink;

interface InventoryLoader
{
    public function loadInventory(Inventory $inventory): void;
}

<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Interlink;

use phpDocumentor\Guides\ReferenceResolvers\AnchorReducer;
use RuntimeException;

use function array_key_exists;

class InventoryRepository
{
    /** @var array<string, Inventory>  */
    private array $inventories = [];

    /** @param array<int, array<string, string>> $inventoryConfigs */
    public function __construct(
        private readonly AnchorReducer $anchorReducer,
        private readonly InventoryLoader $inventoryLoader,
        array $inventoryConfigs,
    ) {
        foreach ($inventoryConfigs as $inventory) {
            $this->inventories[$this->anchorReducer->reduceAnchor($inventory['id'])] = new Inventory($inventory['url']);
        }
    }

    public function hasInventory(string $key): bool
    {
        $reducedKey = $this->anchorReducer->reduceAnchor($key);

        return array_key_exists($reducedKey, $this->inventories);
    }

    public function getInventory(string $key): Inventory
    {
        $reducedKey = $this->anchorReducer->reduceAnchor($key);
        if (!$this->hasInventory($reducedKey)) {
            throw new RuntimeException('Inventory with key ' . $reducedKey . ' not found. ', 1_671_398_986);
        }

        $this->inventoryLoader->loadInventory($this->inventories[$reducedKey]);

        return $this->inventories[$reducedKey];
    }

    public function addInventory(string $key, Inventory $inventory): void
    {
        $reducedKey = $this->anchorReducer->reduceAnchor($key);
        $this->inventories[$reducedKey] = $inventory;
    }
}

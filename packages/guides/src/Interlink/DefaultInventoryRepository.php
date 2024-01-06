<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Interlink;

use phpDocumentor\Guides\Interlink\Exception\InterlinkInventoryNotFound;
use phpDocumentor\Guides\Interlink\Exception\InterlinkNotFound;
use phpDocumentor\Guides\ReferenceResolvers\AnchorReducer;

use function array_key_exists;

final class DefaultInventoryRepository implements InventoryRepository
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
            $this->inventories[$this->anchorReducer->reduceAnchor($inventory['id'])] = new Inventory($inventory['url'], $anchorReducer);
        }
    }

    /** @throws InterlinkNotFound */
    public function getLink(string $inventoryKey, string $groupKey, string $linkKey): InventoryLink
    {
        $inventory = $this->getInventory($inventoryKey);
        $group = $inventory->getGroup($groupKey);

        return $group->getLink($linkKey);
    }

    public function hasInventory(string $key): bool
    {
        $reducedKey = $this->anchorReducer->reduceAnchor($key);

        return array_key_exists($reducedKey, $this->inventories);
    }

    /** @throws InterlinkInventoryNotFound */
    public function getInventory(string $key): Inventory
    {
        $reducedKey = $this->anchorReducer->reduceAnchor($key);
        if (!$this->hasInventory($reducedKey)) {
            throw new InterlinkInventoryNotFound('Inventory with key ' . $reducedKey . ' not found. ', 1_671_398_986);
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

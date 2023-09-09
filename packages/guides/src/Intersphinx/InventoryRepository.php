<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Intersphinx;

use RuntimeException;

use function array_key_exists;
use function strtolower;

class InventoryRepository
{
    /** @var array<string, Inventory>  */
    private array $inventories = [];

    public function __construct(private readonly InventoryLoader $inventoryLoader)
    {
    }

    /** @param array<int, array<string, string>> $inventoryConfigs */
    public function initialize(array $inventoryConfigs): void
    {
        $this->inventories = [];
        foreach ($inventoryConfigs as $inventory) {
            $this->inventories[$inventory['id']] = new Inventory($inventory['url']);
        }
    }

    public function hasInventory(string $key): bool
    {
        $lowerCaseKey = strtolower($key);

        return array_key_exists($lowerCaseKey, $this->inventories);
    }

    public function getInventory(string $key): Inventory
    {
        $lowerCaseKey = strtolower($key);
        if (!$this->hasInventory($lowerCaseKey)) {
            throw new RuntimeException('Inventory with key ' . $lowerCaseKey . ' not found. ', 1_671_398_986);
        }

        $this->inventoryLoader->loadInventory($this->inventories[$lowerCaseKey]);

        return $this->inventories[$lowerCaseKey];
    }

    public function addInventory(string $key, Inventory $inventory): void
    {
        $lowerCaseKey                     = strtolower($key);
        $this->inventories[$lowerCaseKey] = $inventory;
    }
}

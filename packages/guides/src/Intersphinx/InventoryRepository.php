<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Intersphinx;

use RuntimeException;

use function array_key_exists;
use function strtolower;

class InventoryRepository
{
    /** @param array<String, Inventory> $inventories */
    public function __construct(private array $inventories)
    {
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
            throw new RuntimeException('Inventory with key ' . $lowerCaseKey . ' not found. ', 1671398986);
        }

        return $this->inventories[$lowerCaseKey];
    }

    public function addInventory(string $key, Inventory $inventory): void
    {
        $lowerCaseKey                     = strtolower($key);
        $this->inventories[$lowerCaseKey] = $inventory;
    }
}

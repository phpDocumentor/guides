<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Intersphinx;

use function is_array;

final class InventoryLoader
{
    public function __construct(
        private readonly InventoryRepository $inventoryRepository,
        private readonly JsonLoader $jsonLoader,
        private readonly string $pathToJson = 'objects.inv.json',
    ) {
    }

    public function getInventoryRepository(): InventoryRepository
    {
        return $this->inventoryRepository;
    }

    /** @param array<String, mixed> $json */
    public function loadInventoryFromJson(string $key, string $baseUrl, array $json): void
    {
        $newInventory = new Inventory($baseUrl);
        foreach ($json as $groupKey => $groupArray) {
            $group = new InventoryGroup();
            if (is_array($groupArray)) {
                foreach ($groupArray as $linkKey => $linkArray) {
                    if (!is_array($linkArray)) {
                        continue;
                    }

                    $link = new InventoryLink($linkArray[0], $linkArray[1], $linkArray[2], $linkArray[3]);
                    $group->addLink($linkKey, $link);
                }
            }

            $newInventory->addGroup($groupKey, $group);
        }

        $this->inventoryRepository->addInventory($key, $newInventory);
    }

    public function loadInventoryFromUrl(string $key, string $url): void
    {
        $json = $this->jsonLoader->loadJsonFromUrl($url . $this->pathToJson);

        $this->loadInventoryFromJson($key, $url, $json);
    }
}

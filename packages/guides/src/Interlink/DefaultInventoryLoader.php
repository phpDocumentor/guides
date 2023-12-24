<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Interlink;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\ClientException;

use function is_array;
use function strval;

final class DefaultInventoryLoader implements InventoryLoader
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly JsonLoader $jsonLoader,
        private readonly string $pathToJson = 'objects.inv.json',
    ) {
    }

    /** @param array<String, mixed> $json */
    public function loadInventoryFromJson(Inventory $inventory, array $json): void
    {
        foreach ($json as $groupKey => $groupArray) {
            $group = new InventoryGroup();
            if (is_array($groupArray)) {
                foreach ($groupArray as $linkKey => $linkArray) {
                    if (!is_array($linkArray)) {
                        continue;
                    }

                    $link = new InventoryLink($linkArray[0], $linkArray[1], $linkArray[2], $linkArray[3]);
                    $group->addLink(strval($linkKey), $link);
                }
            }

            $inventory->addGroup($groupKey, $group);
        }

        $inventory->setIsLoaded(true);
    }

    public function loadInventory(Inventory $inventory): void
    {
        if ($inventory->isLoaded()) {
            return;
        }

        try {
            $json = $this->jsonLoader->loadJsonFromUrl($inventory->getBaseUrl() . $this->pathToJson);

            $this->loadInventoryFromJson($inventory, $json);
        } catch (ClientException $exception) {
            $this->logger->warning('Interlink inventory not found: ' . $exception->getMessage());
        }
    }
}

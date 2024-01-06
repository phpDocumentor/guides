<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Interlink;

use phpDocumentor\Guides\ReferenceResolvers\AnchorReducer;
use phpDocumentor\Guides\ReferenceResolvers\NullAnchorReducer;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\ClientException;

use function count;
use function is_array;
use function strval;

final class DefaultInventoryLoader implements InventoryLoader
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly JsonLoader $jsonLoader,
        private readonly AnchorReducer $anchorReducer,
        private readonly string $pathToJson = 'objects.inv.json',
    ) {
    }

    /** @param array<String, mixed> $json */
    public function loadInventoryFromJson(Inventory $inventory, array $json): void
    {
        foreach ($json as $groupKey => $groupArray) {
            $groupAnchorReducer = $this->anchorReducer;
            if ($groupKey === 'std:doc') {
                // Do not reduce Document names
                $groupAnchorReducer = new NullAnchorReducer();
            }

            $group = new InventoryGroup($groupAnchorReducer);
            if (is_array($groupArray)) {
                foreach ($groupArray as $linkKey => $linkArray) {
                    if (!is_array($linkArray) || count($linkArray) < 4) {
                        continue;
                    }

                    $reducedLinkKey = $groupAnchorReducer->reduceAnchor(strval($linkKey));
                    $link = new InventoryLink($linkArray[0], $linkArray[1], $linkArray[2], $linkArray[3]);
                    $group->addLink($reducedLinkKey, $link);
                }
            }

            $reducedGroupKey = $this->anchorReducer->reduceAnchor(strval($groupKey));
            $inventory->addGroup($reducedGroupKey, $group);
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

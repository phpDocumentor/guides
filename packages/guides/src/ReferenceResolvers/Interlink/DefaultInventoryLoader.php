<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Guides\ReferenceResolvers\Interlink;

use phpDocumentor\Guides\ReferenceResolvers\AnchorNormalizer;
use phpDocumentor\Guides\ReferenceResolvers\NullAnchorNormalizer;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\ClientException;

use function count;
use function is_array;
use function is_scalar;
use function is_string;
use function sprintf;
use function strval;

final class DefaultInventoryLoader implements InventoryLoader
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly JsonLoader $jsonLoader,
        private readonly AnchorNormalizer $anchorReducer,
        private readonly string $pathToJson = 'objects.inv.json',
    ) {
    }

    /** @param array<String, mixed> $json */
    public function loadInventoryFromJson(Inventory $inventory, array $json): void
    {
        foreach ($json as $groupKey => $groupArray) {
            $groupAnchorNormalizer = $this->anchorReducer;
            if ($groupKey === 'std:doc') {
                // Do not reduce Document names
                $groupAnchorNormalizer = new NullAnchorNormalizer();
            }

            $group = new InventoryGroup($groupAnchorNormalizer);
            if (is_array($groupArray)) {
                foreach ($groupArray as $linkKey => $linkArray) {
                    if (!is_array($linkArray) || count($linkArray) < 4) {
                        $this->logger->warning(sprintf('Invalid Inventory entry found. Each entry in array "%s" MUST be an array with at least 4 entries. ', $groupKey));
                        continue;
                    }

                    if (!is_string($linkArray[2])) {
                        $this->logger->warning(sprintf('Invalid Inventory entry found. Each entry in array "%s" must have a path as third entry.', $groupKey));
                        continue;
                    }

                    if (!is_scalar($linkArray[0] ?? '') || !is_scalar($linkArray[1] ?? '') || !is_scalar($linkArray[3] ?? '')) {
                        $this->logger->warning(sprintf('Invalid Inventory entry found. Only scalar values are allowed in each entry of array "%s". ', $groupKey));
                        continue;
                    }

                    $reducedLinkKey = $groupAnchorNormalizer->reduceAnchor(strval($linkKey));
                    $link = new InventoryLink(strval($linkArray[0] ?? ''), strval($linkArray[1] ?? ''), $linkArray[2], strval($linkArray[3] ?? '-'));
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

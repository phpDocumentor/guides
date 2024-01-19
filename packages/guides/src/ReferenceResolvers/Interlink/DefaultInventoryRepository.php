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

use phpDocumentor\Guides\Nodes\Inline\CrossReferenceNode;
use phpDocumentor\Guides\ReferenceResolvers\AnchorNormalizer;
use phpDocumentor\Guides\ReferenceResolvers\Message;
use phpDocumentor\Guides\ReferenceResolvers\Messages;
use phpDocumentor\Guides\RenderContext;

use function array_key_exists;
use function array_merge;
use function sprintf;

final class DefaultInventoryRepository implements InventoryRepository
{
    /** @var array<string, Inventory>  */
    private array $inventories = [];

    /** @param array<int, array<string, string>> $inventoryConfigs */
    public function __construct(
        private readonly AnchorNormalizer $anchorNormalizer,
        private readonly InventoryLoader $inventoryLoader,
        array $inventoryConfigs,
    ) {
        foreach ($inventoryConfigs as $inventory) {
            $this->inventories[$this->anchorNormalizer->reduceAnchor($inventory['id'])] = new Inventory($inventory['url'], $anchorNormalizer);
        }
    }

    public function getLink(CrossReferenceNode $node, RenderContext $renderContext, Messages $messages): InventoryLink|null
    {
        $inventory = $this->getInventory($node, $renderContext, $messages);
        $group = $inventory?->getGroup($node, $renderContext, $messages);

        return $group?->getLink($node, $renderContext, $messages);
    }

    public function hasInventory(string $key): bool
    {
        $reducedKey = $this->anchorNormalizer->reduceAnchor($key);

        return array_key_exists($reducedKey, $this->inventories);
    }

    public function getInventory(CrossReferenceNode $node, RenderContext $renderContext, Messages $messages): Inventory|null
    {
        $reducedKey = $this->anchorNormalizer->reduceAnchor($node->getInterlinkDomain());
        if (!$this->hasInventory($reducedKey)) {
            $messages->addWarning(
                new Message(
                    sprintf(
                        'Inventory with key %s not found. ',
                        $node->getInterlinkDomain(),
                    ),
                    array_merge($renderContext->getLoggerInformation(), $node->getDebugInformation()),
                ),
            );

            return null;
        }

        $this->inventoryLoader->loadInventory($this->inventories[$reducedKey]);

        return $this->inventories[$reducedKey];
    }

    public function addInventory(string $key, Inventory $inventory): void
    {
        $reducedKey = $this->anchorNormalizer->reduceAnchor($key);
        $this->inventories[$reducedKey] = $inventory;
    }
}

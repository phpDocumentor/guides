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

final class Inventory
{
    /** @var InventoryGroup[]  */
    private array $groups = [];

    private bool $isLoaded = false;

    public function __construct(private readonly string $baseUrl, private readonly AnchorNormalizer $anchorNormalizer)
    {
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function addGroup(string $key, InventoryGroup $group): void
    {
        $reducedKey = $this->anchorNormalizer->reduceAnchor($key);
        $this->groups[$reducedKey] = $group;
    }

    /** @return InventoryGroup[] */
    public function getGroups(): array
    {
        return $this->groups;
    }

    public function getGroup(CrossReferenceNode $node, RenderContext $renderContext, Messages $messages): InventoryGroup|null
    {
        $reducedKey = $this->anchorNormalizer->reduceAnchor($node->getInterlinkGroup());
        if (!$this->hasGroup($reducedKey)) {
            $messages->addWarning(new Message(
                sprintf(
                    'Inventory group with key "%s" (%s) not found in inventory %s. ',
                    $node->getInterlinkGroup(),
                    $reducedKey,
                    $this->baseUrl,
                ),
                array_merge($renderContext->getLoggerInformation(), $node->getDebugInformation()),
            ));

            return null;
        }

        return $this->groups[$reducedKey];
    }

    public function hasGroup(string $key): bool
    {
        $reducedKey = $this->anchorNormalizer->reduceAnchor($key);

        return array_key_exists($reducedKey, $this->groups);
    }

    public function isLoaded(): bool
    {
        return $this->isLoaded;
    }

    public function setIsLoaded(bool $isLoaded): Inventory
    {
        $this->isLoaded = $isLoaded;

        return $this;
    }
}

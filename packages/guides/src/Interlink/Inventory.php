<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Interlink;

use phpDocumentor\Guides\Interlink\Exception\InterlinkGroupNotFound;
use phpDocumentor\Guides\Interlink\Exception\InterlinkNotFound;
use phpDocumentor\Guides\ReferenceResolvers\AnchorNormalizer;

use function array_key_exists;
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

    /** @throws InterlinkNotFound */
    public function getGroup(string $key): InventoryGroup
    {
        $reducedKey = $this->anchorNormalizer->reduceAnchor($key);
        if (!$this->hasGroup($reducedKey)) {
            throw new InterlinkGroupNotFound(
                sprintf('Inventory group with key "%s" (%s) not found. ', $key, $reducedKey),
                1_671_398_986,
            );
        }

        return $this->groups[$reducedKey];
    }

    public function getLink(string $groupKey, string $key): InventoryLink
    {
        return $this->getGroup($groupKey)->getLink($key);
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

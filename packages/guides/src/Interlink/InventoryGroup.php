<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Interlink;

use phpDocumentor\Guides\Interlink\Exception\InterlinkNotFound;
use phpDocumentor\Guides\Interlink\Exception\InterlinkTargetNotFound;
use phpDocumentor\Guides\ReferenceResolvers\AnchorReducer;

use function array_key_exists;
use function sprintf;

final class InventoryGroup
{
    /** @var InventoryLink[]  */
    private array $links = [];

    public function __construct(private readonly AnchorReducer $anchorReducer)
    {
    }

    public function addLink(string $key, InventoryLink $link): void
    {
        $reducedKey = $this->anchorReducer->reduceAnchor($key);
        $this->links[$reducedKey] = $link;
    }

    public function hasLink(string $key): bool
    {
        $reducedKey = $this->anchorReducer->reduceAnchor($key);

        return array_key_exists($reducedKey, $this->links);
    }

    /** @throws InterlinkNotFound */
    public function getLink(string $key): InventoryLink
    {
        $reducedKey = $this->anchorReducer->reduceAnchor($key);
        if (!array_key_exists($reducedKey, $this->links)) {
            throw new InterlinkTargetNotFound(sprintf('Inventory link with key "%s" (%s) not found. ', $key, $reducedKey), 1_671_398_986);
        }

        return $this->links[$reducedKey];
    }
}

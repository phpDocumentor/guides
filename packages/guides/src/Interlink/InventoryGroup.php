<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Interlink;

use RuntimeException;

use function array_key_exists;
use function levenshtein;
use function strtolower;

final class InventoryGroup
{
    /** @var InventoryLink[]  */
    private array $links = [];

    public function addLink(string $key, InventoryLink $link): void
    {
        $lowerCaseKey               = strtolower($key);
        $this->links[$lowerCaseKey] = $link;
    }

    public function hasLink(string $key): bool
    {
        $lowerCaseKey = strtolower($key);

        return array_key_exists($lowerCaseKey, $this->links);
    }

    public function getLink(string $key): InventoryLink
    {
        $lowerCaseKey = strtolower($key);
        if (!array_key_exists($lowerCaseKey, $this->links)) {
            throw new RuntimeException('Inventory link with key ' . $lowerCaseKey . ' not found. ', 1_671_398_986);
        }

        return $this->links[$lowerCaseKey];
    }

    public function getProposedLink(string $key): InventoryLink|null
    {
        $shortestDistance = -1;
        $closestMatch = null;
        $closestKey = '';

        foreach ($this->links as $realKey => $link) {
            $distance = levenshtein($realKey, $key);

            // If the distance is shorter than the current shortest distance or the shortest distance is not set
            if ($distance >= $shortestDistance && $shortestDistance !== -1) {
                continue;
            }

            $shortestDistance = $distance;
            $closestMatch = $link;
            $closestKey = $realKey;
        }

        if ($closestMatch !== null) {
            $closestMatch->setOriginalKey($closestKey);
        }

        return $closestMatch;
    }
}

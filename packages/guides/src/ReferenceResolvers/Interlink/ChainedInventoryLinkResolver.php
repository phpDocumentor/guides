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

use Doctrine\Deprecations\Deprecation;
use phpDocumentor\Guides\Nodes\Inline\CrossReferenceNode;
use phpDocumentor\Guides\ReferenceResolvers\Message;
use phpDocumentor\Guides\ReferenceResolvers\Messages;
use phpDocumentor\Guides\RenderContext;

use function array_key_exists;
use function array_merge;
use function sprintf;

final class ChainedInventoryLinkResolver implements InventoryLinkResolver
{
    /** @var array<string, InventoryRepository|null> */
    private array $cachedRepositories = [];

    /** @param iterable<InventoryRepository> $repositories */
    public function __construct(
        private readonly iterable $repositories,
    ) {
    }

    public function getLink(CrossReferenceNode $node, RenderContext $renderContext, Messages $messages): InventoryLink|null
    {
        return $this->resolveInventoryLink($node, $renderContext, $messages)?->getLink();
    }

    public function hasInventory(string $key): bool
    {
        return $this->findInventoryRepository($key) !== null;
    }

    public function getInventory(CrossReferenceNode $node, RenderContext $renderContext, Messages $messages): Inventory|null
    {
        Deprecation::trigger(
            'phpDocumentor/guides',
            'https://github.com/phpDocumentor/guides/issues',
            'InventoryRepository::getInventory() is deprecated. Implement '
            . 'InventoryLinkResolver::resolveInventoryLink() for one-call interlink resolution.',
        );

        return $this->findInventoryRepository($node->getInterlinkDomain())?->getInventory($node, $renderContext, $messages);
    }

    public function resolveInventoryLink(
        CrossReferenceNode $node,
        RenderContext $renderContext,
        Messages $messages,
    ): ResolvedInventoryLink|null {
        $repository = $this->findInventoryRepository($node->getInterlinkDomain());
        if ($repository === null) {
            $messages->addWarning(
                new Message(
                    sprintf('Inventory with key %s not found. ', $node->getInterlinkDomain()),
                    array_merge($renderContext->getLoggerInformation(), $node->getDebugInformation()),
                ),
            );

            return null;
        }

        if ($repository instanceof InventoryLinkResolver) {
            return $repository->resolveInventoryLink($node, $renderContext, $messages);
        }

        $inventory = $repository->getInventory($node, $renderContext, $messages);
        $link = $repository->getLink($node, $renderContext, $messages);
        if ($inventory === null || $link === null) {
            return null;
        }

        return new ResolvedInventoryLink($inventory->getBaseUrl(), $link);
    }

    private function findInventoryRepository(string $key): InventoryRepository|null
    {
        if (array_key_exists($key, $this->cachedRepositories)) {
            return $this->cachedRepositories[$key];
        }

        foreach ($this->repositories as $repository) {
            if ($repository->hasInventory($key)) {
                $this->cachedRepositories[$key] = $repository;

                return $repository;
            }
        }

        $this->cachedRepositories[$key] = null;

        return null;
    }
}

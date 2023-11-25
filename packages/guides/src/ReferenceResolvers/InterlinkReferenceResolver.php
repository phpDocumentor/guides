<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers;

use phpDocumentor\Guides\Interlink\InventoryRepository;
use phpDocumentor\Guides\Nodes\Inline\CrossReferenceNode;
use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;
use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\RenderContext;
use Psr\Log\LoggerInterface;

use function array_merge;
use function sprintf;

class InterlinkReferenceResolver implements ReferenceResolver
{
    public final const PRIORITY = 50;

    public function __construct(
        private readonly InventoryRepository $inventoryRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function resolve(LinkInlineNode $node, RenderContext $renderContext): bool
    {
        if (!$node instanceof CrossReferenceNode || $node->getInterlinkDomain() === '') {
            return false;
        }

        $domain = $node->getInterlinkDomain();
        $target = $node->getTargetReference();
        if (!$this->inventoryRepository->hasInventory($domain)) {
            $this->logger->warning(
                sprintf(
                    'Inventory with name "%s" could not be resolved in file "%s". ',
                    $domain,
                    $renderContext->getCurrentFileName(),
                ),
                array_merge($renderContext->getLoggerInformation(), $node->getDebugInformation()),
            );

            return false;
        }

        $inventory = $this->inventoryRepository->getInventory($domain);
        $group = $node instanceof DocReferenceNode ? 'std:doc' : 'std:label';
        if (!$inventory->hasInventoryGroup($group)) {
            $this->logger->warning(
                sprintf(
                    'Inventory with name "%s" does not contain group %s, required in file "%s". ',
                    $domain,
                    $group,
                    $renderContext->getCurrentFileName(),
                ),
                array_merge($renderContext->getLoggerInformation(), $node->getDebugInformation()),
            );

            return false;
        }

        $inventoryGroup = $inventory->getInventory($group);
        if (!$inventoryGroup->hasLink($target)) {
            $this->logger->warning(
                sprintf(
                    'Link with name "%s:%s" not found in group "%s", required in file "%s".',
                    $domain,
                    $target,
                    $group,
                    $renderContext->getCurrentFileName(),
                ),
                array_merge($renderContext->getLoggerInformation(), $node->getDebugInformation()),
            );

            return false;
        }

        $link = $inventory->getLink($group, $target);

        $node->setUrl($inventory->getBaseUrl() . $link->getPath());
        if ($node->getValue() === '') {
            $node->setValue($link->getTitle());
        }

        return true;
    }

    public static function getPriority(): int
    {
        return self::PRIORITY;
    }
}

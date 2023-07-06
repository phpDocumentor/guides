<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers;

use phpDocumentor\Guides\Intersphinx\InventoryRepository;
use phpDocumentor\Guides\Nodes\Inline\CrossReferenceNode;
use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;
use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\RenderContext;

use function explode;
use function str_contains;

class IntersphinxReferenceResolver implements ReferenceResolver
{
    public final const PRIORITY = 50;

    public function __construct(private readonly InventoryRepository $inventoryRepository)
    {
    }

    public function resolve(LinkInlineNode $node, RenderContext $renderContext): bool
    {
        if (!$node instanceof CrossReferenceNode || !str_contains($node->getTargetReference(), ':')) {
            return false;
        }

        [$domain, $target] = explode(':', $node->getTargetReference(), 2);
        if (!$this->inventoryRepository->hasInventory($domain)) {
            return false;
        }

        $inventory = $this->inventoryRepository->getInventory($domain);
        $group = $node instanceof DocReferenceNode ? 'std:doc' : 'std:label';
        if (!$inventory->hasInventoryGroup($group)) {
            return false;
        }

        $inventoryGroup = $inventory->getInventory($group);
        if (!$inventoryGroup->hasLink($target)) {
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

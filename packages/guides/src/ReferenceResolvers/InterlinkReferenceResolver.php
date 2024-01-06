<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers;

use phpDocumentor\Guides\Interlink\Exception\InterlinkGroupNotFound;
use phpDocumentor\Guides\Interlink\Exception\InterlinkInventoryNotFound;
use phpDocumentor\Guides\Interlink\Exception\InterlinkTargetNotFound;
use phpDocumentor\Guides\Interlink\InventoryRepository;
use phpDocumentor\Guides\Nodes\Inline\CrossReferenceNode;
use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;
use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\RenderContext;

use function sprintf;

class InterlinkReferenceResolver implements ReferenceResolver
{
    public final const PRIORITY = 50;

    public function __construct(
        private readonly InventoryRepository $inventoryRepository,
    ) {
    }

    public function resolve(LinkInlineNode $node, RenderContext $renderContext, Messages $messages): bool
    {
        if (!$node instanceof CrossReferenceNode || $node->getInterlinkDomain() === '') {
            return false;
        }

        $domain = $node->getInterlinkDomain();
        $target = $node->getTargetReference();
        $group = $node instanceof DocReferenceNode ? 'std:doc' : 'std:label';

        try {
            $inventory = $this->inventoryRepository->getInventory($domain);
            $link = $this->inventoryRepository->getLink($domain, $group, $target);
        } catch (InterlinkInventoryNotFound) {
            $messages->addWarning(
                new Message(
                    sprintf(
                        'Inventory with name "%s" could not be resolved in file "%s". ',
                        $domain,
                        $renderContext->getCurrentFileName(),
                    ),
                    $node->getDebugInformation(),
                ),
            );

            return false;
        } catch (InterlinkGroupNotFound) {
            $messages->addWarning(new Message(
                sprintf(
                    'Inventory with name "%s" does not contain group %s, required in file "%s". ',
                    $domain,
                    $group,
                    $renderContext->getCurrentFileName(),
                ),
                $node->getDebugInformation(),
            ));

            return false;
        } catch (InterlinkTargetNotFound) {
            $messages->addWarning(new Message(
                sprintf(
                    'Link with name "%s:%s" not found in group "%s", required in file "%s".',
                    $domain,
                    $target,
                    $group,
                    $renderContext->getCurrentFileName(),
                ),
                $node->getDebugInformation(),
            ));

            return false;
        }

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

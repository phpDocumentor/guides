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

final class InventoryGroup
{
    /** @var InventoryLink[]  */
    private array $links = [];

    public function __construct(private readonly AnchorNormalizer $anchorNormalizer)
    {
    }

    public function addLink(string $key, InventoryLink $link): void
    {
        $reducedKey = $this->anchorNormalizer->reduceAnchor($key);
        $this->links[$reducedKey] = $link;
    }

    public function hasLink(string $key): bool
    {
        $reducedKey = $this->anchorNormalizer->reduceAnchor($key);

        return array_key_exists($reducedKey, $this->links);
    }

    public function getLink(CrossReferenceNode $node, RenderContext $renderContext, Messages $messages): InventoryLink|null
    {
        $reducedKey = $this->anchorNormalizer->reduceAnchor($node->getTargetReference());
        if (!array_key_exists($reducedKey, $this->links)) {
            $messages->addWarning(
                new Message(
                    sprintf(
                        'Inventory link with key "%s:%s" (%s) not found. ',
                        $node->getInterlinkDomain(),
                        $node->getTargetReference(),
                        $reducedKey,
                    ),
                    array_merge($renderContext->getLoggerInformation(), $node->getDebugInformation()),
                ),
            );

            return null;
        }

        return $this->links[$reducedKey];
    }
}

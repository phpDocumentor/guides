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
use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;
use phpDocumentor\Guides\ReferenceResolvers\AnchorNormalizer;
use phpDocumentor\Guides\ReferenceResolvers\Message;
use phpDocumentor\Guides\ReferenceResolvers\Messages;
use phpDocumentor\Guides\RenderContext;

use function array_key_exists;
use function array_merge;
use function explode;
use function sprintf;
use function str_contains;

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
        $targetReference = $node->getTargetReference();
        $anchor = '';
        if ($node instanceof DocReferenceNode && str_contains($targetReference, '#')) {
            $exploded = explode('#', $targetReference, 2);
            $targetReference = $exploded[0];
            $anchor = '#' . $exploded[1];
        }

        $reducedKey = $this->anchorNormalizer->reduceAnchor($targetReference);
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

        $link = $this->links[$reducedKey];
        if ($anchor !== '') {
            $link = $link->withPath($link->getPath() . $anchor);
        }

        return $link;
    }
}

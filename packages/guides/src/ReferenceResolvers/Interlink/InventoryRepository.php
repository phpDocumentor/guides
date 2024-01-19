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
use phpDocumentor\Guides\ReferenceResolvers\Messages;
use phpDocumentor\Guides\RenderContext;

interface InventoryRepository
{
    public function getLink(CrossReferenceNode $node, RenderContext $renderContext, Messages $messages): InventoryLink|null;

    public function hasInventory(string $key): bool;

    public function getInventory(CrossReferenceNode $node, RenderContext $renderContext, Messages $messages): Inventory|null;
}

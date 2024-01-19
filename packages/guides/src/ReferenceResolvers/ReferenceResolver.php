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

namespace phpDocumentor\Guides\ReferenceResolvers;

use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\RenderContext;

interface ReferenceResolver
{
    /** @return bool true if the reference is resolved */
    public function resolve(LinkInlineNode $node, RenderContext $renderContext, Messages $messages): bool;

    public static function getPriority(): int;
}

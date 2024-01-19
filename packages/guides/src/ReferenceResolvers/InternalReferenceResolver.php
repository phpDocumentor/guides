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

final class InternalReferenceResolver implements ReferenceResolver
{
    public final const PRIORITY = 100;

    public function resolve(LinkInlineNode $node, RenderContext $renderContext, Messages $messages): bool
    {
        $link = $renderContext->getLink($node->getTargetReference());
        if ($link) {
            $node->setUrl($link);

            return true;
        }

        return false;
    }

    public static function getPriority(): int
    {
        return self::PRIORITY;
    }
}

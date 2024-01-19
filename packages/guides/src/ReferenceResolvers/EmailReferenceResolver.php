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

use function filter_var;

use const FILTER_VALIDATE_EMAIL;

/**
 * Resolves references with an embedded email address
 */
final class EmailReferenceResolver implements ReferenceResolver
{
    public final const PRIORITY = -100;

    public function resolve(LinkInlineNode $node, RenderContext $renderContext, Messages $messages): bool
    {
        if (filter_var($node->getTargetReference(), FILTER_VALIDATE_EMAIL)) {
            $node->setUrl('mailto:' . $node->getTargetReference());

            return true;
        }

        return false;
    }

    public static function getPriority(): int
    {
        return self::PRIORITY;
    }
}

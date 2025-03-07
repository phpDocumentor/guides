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

namespace phpDocumentor\Guides\Nodes\Inline;

use Doctrine\Deprecations\Deprecation;

use function func_get_arg;
use function func_num_args;
use function is_string;

/**
 * Represents a link to an external source or email
 */
final class HyperLinkNode extends AbstractLinkInlineNode
{
    /** @param InlineNodeInterface[] $children */
    public function __construct(string|array $children, string $targetReference)
    {
        if (is_string($children)) {
            Deprecation::trigger(
                'phpdocumentor/guides',
                'https://github.com/phpDocumentor/guides/issues/1161',
                'Passing the content of %s as string is deprecated, pass an array of InlineNodeInterface instances instead. New signature: array $children, string $targetReference',
                static::class,
            );

            if (func_num_args() < 3) {
                // compat with (string $value, string $targetReference) signature
                $children = $children === '' ? [] : [new PlainTextInlineNode($children)];
            } else {
                // compat with (string $value, string $targetReference, array $children = []) signature
                /** @var InlineNodeInterface[] $children */
                $children = func_get_arg(2);
            }
        }

        parent::__construct('link', $targetReference, $children);
    }
}

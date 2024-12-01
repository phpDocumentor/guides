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
use phpDocumentor\Guides\Nodes\InlineCompoundNode;

use function func_get_arg;
use function func_num_args;
use function is_string;

final class StrongInlineNode extends InlineCompoundNode
{
    use BCInlineNodeBehavior;

    public const TYPE = 'strong';

    /** @param InlineNodeInterface[] $children */
    public function __construct(string|array $children = [])
    {
        if (is_string($children)) {
            Deprecation::trigger(
                'phpdocumentor/guides',
                'https://github.com/phpDocumentor/guides/issues/1161',
                'Passing the content of %s as string is deprecated, pass an array of InlineNodeInterface instances instead. New signature: array $children',
                static::class,
            );

            if (func_num_args() < 2) {
                // compat with (string $value) signature
                $children = $children === '' ? [] : [new PlainTextInlineNode($children)];
            } else {
                // compat with (string $value, array $children = []) signature
                /** @var InlineNodeInterface[] $children */
                $children = func_get_arg(1);
            }
        }

        parent::__construct($children);
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}

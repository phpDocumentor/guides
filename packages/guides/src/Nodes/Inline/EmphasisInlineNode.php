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

final class EmphasisInlineNode extends InlineCompoundNode
{
    use BCInlineNodeBehavior;

    public const TYPE = 'emphasis';

    /** @param InlineNodeInterface[] $children */
    public function __construct(string $value, array $children = [])
    {
        if (empty($children)) {
            $children = [new PlainTextInlineNode($value)];
            Deprecation::trigger(
                'phpdocumentor/guides',
                'https://github.com/phpDocumentor/guides/issues/1161',
                'Please provide the children as an array of InlineNodeInterface instances instead of a string.',
            );
        }

        parent::__construct($children);
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}

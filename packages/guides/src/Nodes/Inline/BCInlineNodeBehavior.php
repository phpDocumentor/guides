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

use function is_string;

trait BCInlineNodeBehavior
{
    public function getValue(): string
    {
        Deprecation::trigger(
            'phpdocumentor/guides',
            'https://github.com/phpDocumentor/guides/issues/1161',
            'Use getChildren to access the value of %s.',
            static::class,
        );

        return $this->toString();
    }

    /** @param InlineNodeInterface[]|string $value */
    public function setValue(mixed $value): void
    {
        if (is_string($value)) {
            $value = $value === '' ? [] : [new PlainTextInlineNode($value)];

            Deprecation::trigger(
                'phpdocumentor/guides',
                'https://github.com/phpDocumentor/guides/issues/1161',
                'Passing a string to %s is deprecated, pass an array of InlineNodeInterface instances instead.',
                __METHOD__,
            );
        }

        parent::setValue($value);
    }

    abstract public function toString(): string;
}

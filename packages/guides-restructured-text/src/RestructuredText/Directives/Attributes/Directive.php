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

namespace phpDocumentor\Guides\RestructuredText\Directives\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class Directive
{
    /** @param string[] $aliases */
    public function __construct(
        public readonly string $name,
        public readonly array $aliases = [],
    ) {
    }
}

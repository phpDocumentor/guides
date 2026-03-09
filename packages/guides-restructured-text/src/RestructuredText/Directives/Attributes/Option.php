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
use phpDocumentor\Guides\RestructuredText\Directives\OptionType;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class Option
{
    public function __construct(
        public readonly string $name,
        public readonly OptionType $type = OptionType::String,
        public readonly mixed $default = null,
        public readonly string $description = '',
        public readonly string|null $example = null,
    ) {
    }
}

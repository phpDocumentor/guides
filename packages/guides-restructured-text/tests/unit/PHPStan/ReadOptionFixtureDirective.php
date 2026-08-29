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

namespace phpDocumentor\Guides\RestructuredText\PHPStan;

use phpDocumentor\Guides\RestructuredText\Directives\Attributes\Option;
use phpDocumentor\Guides\RestructuredText\Directives\BaseDirective;
use phpDocumentor\Guides\RestructuredText\Directives\OptionType;

#[Option(name: 'name', type: OptionType::String, description: 'A string option without a default')]
#[Option(name: 'title', type: OptionType::String, default: 'Default title', description: 'A string option with a default')]
#[Option(name: 'count', type: OptionType::Integer, default: 4, description: 'An integer option with a default')]
#[Option(name: 'enabled', type: OptionType::Boolean, default: false, description: 'A boolean option with a default')]
#[Option(name: 'tags', type: OptionType::Array, description: 'An array option without a default')]
#[Option(name: 'float', type: OptionType::String, default: 1.5, description: 'A string option with a float default')]
final class ReadOptionFixtureDirective extends BaseDirective
{
    public function getName(): string
    {
        return 'fixture';
    }
}

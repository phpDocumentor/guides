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

namespace phpDocumentor\Guides\RestructuredText\Architecture;

use PHPat\Selector\Selector;
use PHPat\Test\Builder\Rule;
use PHPat\Test\PHPat;
use phpDocumentor\Guides\RestructuredText\Directives\Attributes\Directive;
use phpDocumentor\Guides\RestructuredText\Directives\BaseDirective;

/**
 * Every concrete directive must declare itself through the `#[Directive]` attribute instead of
 * relying on the legacy, not-upgraded registration path (see `BaseDirective::isUpgraded()`).
 *
 * Abstract intermediate base classes (e.g. `SubDirective`, `ActionDirective`,
 * `AbstractAdmonitionDirective`) are excluded, as they are never instantiated directly and
 * therefore never need the attribute themselves.
 */
final class DirectiveArchitectureTest
{
    public function test_concrete_directives_have_the_directive_attribute(): Rule
    {
        return PHPat::rule()
            ->classes(
                Selector::AllOf(
                    Selector::extends(BaseDirective::class),
                    Selector::Not(Selector::isAbstract()),
                ),
            )
            ->should()
            ->applyAttribute()
            ->classes(Selector::classname(Directive::class))
            ->because('every concrete directive must declare its name via #[Directive(...)] instead of the legacy registration path');
    }

    public function test_directives_should_be_named_correctly(): Rule
    {
        return PHPat::rule()
            ->classes(
                Selector::AllOf(
                    Selector::extends(BaseDirective::class),
                ),
            )
            ->should()->beNamed('/(.+)Directive/', true)
            ->because('Directive classes should be named with the suffix "Directive" to indicate their purpose and maintain consistency across the codebase');
    }
}

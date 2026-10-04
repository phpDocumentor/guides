<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Architecture;

use PHPat\Selector\Selector;
use PHPat\Test\Builder\Rule;
use PHPat\Test\PHPat;

/**
 * Boundary rules for the `guides` core package.
 *
 * Mirrors the former deptrac "Guides" layer: the core engine must not depend on any of the
 * packages built on top of it.
 */
final class GuidesArchitectureTest
{
    public function test_guides_does_not_depend_on_other_packages(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::withFilepath('#/packages/guides/src/#', true))
            ->excluding(
                // Wires the core engine, RST and Markdown together for demo purposes only.
                Selector::withFilepath('#/packages/guides/src/Setup/QuickStart\.php$#', true),
            )
            ->shouldNot()
            ->dependOn()
            ->classes(
                Selector::withFilepath('#/packages/guides-cli/src/#', true),
                Selector::withFilepath('#/packages/guides-restructured-text/src/#', true),
                Selector::withFilepath('#/packages/guides-markdown/src/#', true),
                Selector::withFilepath('#/packages/guides-graphs/src/#', true),
            )
            ->because('the core engine must stay independent of the packages built on top of it');
    }
}

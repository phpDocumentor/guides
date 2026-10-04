<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Architecture;

use PHPat\Selector\Selector;
use PHPat\Test\Builder\Rule;
use PHPat\Test\PHPat;

/**
 * Boundary rules for the `guides-graphs` package.
 *
 * Mirrors the former deptrac "Graphs" layer: Graphs may depend on the core engine and RST, but
 * nothing else.
 */
final class GraphsArchitectureTest
{
    public function test_graphs_does_not_depend_on_cli_or_markdown(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::withFilepath('#/packages/guides-graphs/src/#', true))
            ->shouldNot()
            ->dependOn()
            ->classes(
                Selector::withFilepath('#/packages/guides-cli/src/#', true),
                Selector::withFilepath('#/packages/guides-markdown/src/#', true),
            )
            ->because('the Graphs package may only depend on the core engine and RST');
    }
}

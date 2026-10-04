<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Architecture;

use PHPat\Selector\Selector;
use PHPat\Test\Builder\Rule;
use PHPat\Test\PHPat;

/**
 * Boundary rules for the `guides-restructured-text` package.
 *
 * Mirrors the former deptrac "RST" layer: RST may only depend on the core engine.
 */
final class RestructuredTextArchitectureTest
{
    public function test_rst_does_not_depend_on_cli_markdown_or_graphs(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::withFilepath('#/packages/guides-restructured-text/src/#', true))
            ->shouldNot()
            ->dependOn()
            ->classes(
                Selector::withFilepath('#/packages/guides-cli/src/#', true),
                Selector::withFilepath('#/packages/guides-markdown/src/#', true),
                Selector::withFilepath('#/packages/guides-graphs/src/#', true),
            )
            ->because('the RST package may only depend on the core engine');
    }
}

<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Architecture;

use PHPat\Selector\Selector;
use PHPat\Test\Builder\Rule;
use PHPat\Test\PHPat;

/**
 * Boundary rules for the `guides-markdown` package.
 *
 * Mirrors the former deptrac "Markdown" layer: Markdown may only depend on the core engine.
 */
final class MarkdownArchitectureTest
{
    public function test_markdown_does_not_depend_on_cli_rst_or_graphs(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::withFilepath('#/packages/guides-markdown/src/#', true))
            ->shouldNot()
            ->dependOn()
            ->classes(
                Selector::withFilepath('#/packages/guides-cli/src/#', true),
                Selector::withFilepath('#/packages/guides-restructured-text/src/#', true),
                Selector::withFilepath('#/packages/guides-graphs/src/#', true),
            )
            ->because('the Markdown package may only depend on the core engine');
    }
}

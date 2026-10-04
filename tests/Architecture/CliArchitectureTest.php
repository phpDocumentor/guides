<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Architecture;

use PHPat\Selector\Selector;
use PHPat\Test\Builder\Rule;
use PHPat\Test\PHPat;

/**
 * Boundary rules for the `guides-cli` package.
 *
 * Mirrors the former deptrac "CLI" layer: the CLI may orchestrate the core engine, RST and
 * Markdown, but nothing else.
 */
final class CliArchitectureTest
{
    public function test_cli_does_not_depend_on_graphs(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::withFilepath('#/packages/guides-cli/src/#', true))
            ->shouldNot()
            ->dependOn()
            ->classes(
                Selector::withFilepath('#/packages/guides-graphs/src/#', true),
            )
            ->because('the CLI package may only orchestrate the core engine, RST and Markdown packages');
    }
}

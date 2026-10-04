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

namespace phpDocumentor\Guides\Architecture;

use PHPat\Selector\Selector;
use PHPat\Test\Builder\Rule;
use PHPat\Test\PHPat;

/**
 * Boundary rules for the `guides-cli` package.
 *
 * The CLI may depend on itself, the core engine, RST, Markdown, the shared
 * filesystem/dev-server infrastructure packages, or code that lives outside this repository
 * (vendor libraries, PHP built-ins).
 */
final class CliArchitectureTest
{
    public function test_cli_can_only_depend_on_guides_rst_and_markdown(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::withFilepath('#/packages/guides-cli/src/#', true))
            ->canOnly()
            ->dependOn()
            ->classes(
                Selector::withFilepath('#/packages/guides-cli/src/#', true),
                Selector::withFilepath('#/packages/guides/src/#', true),
                Selector::withFilepath('#/packages/guides-restructured-text/src/#', true),
                Selector::withFilepath('#/packages/guides-markdown/src/#', true),
                Selector::withFilepath('#/packages/filesystem/src/#', true),
                Selector::withFilepath('#/packages/dev-server/src/#', true),
                Selector::Not(Selector::withFilepath('#/packages/#', true)),
            )
            ->because('the CLI package may only orchestrate the core engine, RST and Markdown packages, or external code');
    }
}

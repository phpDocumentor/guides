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
 * Boundary rules for the `guides` core package.
 *
 * The core engine may only depend on itself, the shared filesystem/dev-server infrastructure
 * packages, or code that lives outside this repository (vendor libraries, PHP built-ins); it
 * must stay independent of every package built on top of it (CLI, RST, Markdown, Graphs).
 */
final class GuidesArchitectureTest
{
    public function test_guides_can_only_depend_on_itself(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::withFilepath('#/packages/guides/src/#', true))
            ->canOnly()
            ->dependOn()
            ->classes(
                Selector::withFilepath('#/packages/guides/src/#', true),
                Selector::withFilepath('#/packages/filesystem/src/#', true),
                Selector::withFilepath('#/packages/dev-server/src/#', true),
                Selector::Not(Selector::withFilepath('#/packages/#', true)),
            )
            ->because('the core engine must stay independent of the packages built on top of it');
    }
}

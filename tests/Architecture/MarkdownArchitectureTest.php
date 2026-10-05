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
 * Boundary rules for the `guides-markdown` package.
 *
 * Markdown may only depend on itself, the core engine, or code that lives outside this
 * repository (vendor libraries, PHP built-ins).
 */
final class MarkdownArchitectureTest
{
    public function test_markdown_can_only_depend_on_guides(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::withFilepath('#/packages/guides-markdown/src/#', true))
            ->canOnly()
            ->dependOn()
            ->classes(
                Selector::withFilepath('#/packages/guides-markdown/src/#', true),
                Selector::withFilepath('#/packages/guides/src/#', true),
                Selector::Not(Selector::withFilepath('#/packages/#', true)),
            )
            ->because('the Markdown package may only depend on the core engine or external code');
    }
}

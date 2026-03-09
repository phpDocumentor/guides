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

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;

/**
 * This directive is used to document that a feature changed behavior in a specific version.
 *
 * Basic usage
 *
 * ```rst
 *   .. version-changed:: 2.2
 *
 *       Some feature changed, prior to 2.2 it behaved differently.
 * ```
 *
 * The legacy name `versionchanged` is supported as an alias.
 */
#[Attributes\Directive(name: 'version-changed', aliases: ['versionchanged'])]
final class VersionChangedDirective extends AbstractVersionChangeDirective
{
    public function __construct(protected Rule $startingRule)
    {
        parent::__construct($startingRule, 'version-changed', 'versionchanged', 'Changed in version %s');
    }
}

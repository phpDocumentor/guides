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
 * This directive is used to document that a feature was removed in a specific version.
 *
 * Basic usage
 *
 * ```rst
 *   .. version-removed:: 3.0
 *
 *       This feature was removed.
 * ```
 *
 * The legacy name `versionremoved` is supported as an alias.
 */
#[Attributes\Directive(name: 'version-removed', aliases: ['versionremoved'])]
final class VersionRemovedDirective extends AbstractVersionChangeDirective
{
    public function __construct(protected Rule $startingRule)
    {
        parent::__construct($startingRule, 'version-removed', 'Removed in version %s');
    }
}

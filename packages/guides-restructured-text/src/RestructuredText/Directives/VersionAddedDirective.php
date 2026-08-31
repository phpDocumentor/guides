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
 * This directive is used to document that a feature was added in a specific version.
 *
 * Basic usage
 *
 * ```rst
 *   .. version-added:: 2.0
 *
 *       Some feature was introduced.
 * ```
 *
 * The legacy name `versionadded` is supported as an alias.
 */
final class VersionAddedDirective extends AbstractVersionChangeDirective
{
    public function __construct(protected Rule $startingRule)
    {
        parent::__construct($startingRule, 'version-added', 'versionadded', 'New in version %s');
    }

    /** {@inheritDoc} */
    public function getAliases(): array
    {
        return ['versionadded'];
    }
}

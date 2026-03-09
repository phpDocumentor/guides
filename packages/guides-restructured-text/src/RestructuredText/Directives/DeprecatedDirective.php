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
 * This directive is used to document that a feature was deprecated in a specific version.
 *
 * Basic usage
 *
 * ```rst
 *   .. version-deprecated:: 2.4
 *
 *       Don't use this feature, it'll be removed in 3.0.
 * ```
 *
 * The legacy name `deprecated` is supported as an alias.
 */
#[Attributes\Directive(name: 'version-deprecated', aliases: ['deprecated'])]
final class DeprecatedDirective extends AbstractVersionChangeDirective
{
    public function __construct(protected Rule $startingRule)
    {
        parent::__construct($startingRule, 'version-deprecated', 'deprecated', 'Deprecated since version %s');
    }
}

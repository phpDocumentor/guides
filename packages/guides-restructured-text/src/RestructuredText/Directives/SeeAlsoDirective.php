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
 * Directive to create a seealso admonition.
 *
 * Example:
 *
 * ```rest
 *  .. seealso::
 *
 *      This is a seealso admonition.
 * ```
 */
final class SeeAlsoDirective extends AbstractAdmonitionDirective
{
    public function __construct(protected Rule $startingRule)
    {
        parent::__construct($startingRule, 'seealso', 'See also');
    }
}

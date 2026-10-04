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

use phpDocumentor\Guides\RestructuredText\Directives\Attributes as RST;

/**
 * Todo directives are treated as comments, omitting all content or options
 */
#[RST\Directive(name: 'todo')]
final class TodoDirective extends BaseDirective
{
}

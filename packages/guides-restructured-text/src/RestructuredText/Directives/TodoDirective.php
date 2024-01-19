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

use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

/**
 * Todo directives are treated as comments, omitting all content or options
 */
final class TodoDirective extends ActionDirective
{
    public function getName(): string
    {
        return 'todo';
    }

    public function processAction(BlockContext $blockContext, Directive $directive): void
    {
        // Todo directives are treated as comments
    }
}

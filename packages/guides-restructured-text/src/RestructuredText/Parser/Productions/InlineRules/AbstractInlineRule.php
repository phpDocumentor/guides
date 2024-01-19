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

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\RestructuredText\Parser\InlineLexer;

abstract class AbstractInlineRule implements InlineRule
{
    protected function rollback(InlineLexer $lexer, int $position): void
    {
        $lexer->resetPosition($position);
        $lexer->moveNext();
        $lexer->moveNext();
    }
}

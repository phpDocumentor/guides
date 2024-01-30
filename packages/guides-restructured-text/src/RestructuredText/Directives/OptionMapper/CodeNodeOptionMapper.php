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

namespace phpDocumentor\Guides\RestructuredText\Directives\OptionMapper;

use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\DirectiveOption;

/**
 * The directives `code-block` and `literalinclude` both create a CodeNode with the same possible options.
 * This common mapper is used by both Directives.
 */
interface CodeNodeOptionMapper
{
    /** @param DirectiveOption[] $directiveOptions */
    public function apply(
        CodeNode $codeNode,
        array $directiveOptions,
        BlockContext $blockContext,
    ): void;
}

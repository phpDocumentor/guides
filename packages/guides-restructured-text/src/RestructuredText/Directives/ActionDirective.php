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

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

/**
 * Extend this class to create a directive that does some actions, for example on the parser context, without
 * creating a node.
 */
abstract class ActionDirective extends BaseDirective
{
    public function process(
        BlockContext $blockContext,
        Directive $directive,
    ): Node|null {
        $this->processAction($blockContext, $directive);

        return null;
    }

    /**
     * @param BlockContext $blockContext the current document context with the content
     *    of the directive
     * @param Directive $directive parsed directive containing options and variable
     */
    abstract public function processAction(
        BlockContext $blockContext,
        Directive $directive,
    ): void;
}

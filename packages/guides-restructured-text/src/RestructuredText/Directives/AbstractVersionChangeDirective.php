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

use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;
use phpDocumentor\Guides\RestructuredText\Nodes\VersionChangeNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;

/** @see https://www.sphinx-doc.org/en/master/usage/restructuredtext/directives.html#directive-versionadded */
abstract class AbstractVersionChangeDirective extends SubDirective
{
    /** @param string $type the type passed to VersionChangeNode, also used as the rendered CSS class */
    public function __construct(
        protected Rule $startingRule,
        private readonly string $type,
        private readonly string $label,
    ) {
        parent::__construct($startingRule);
    }

    /** {@inheritDoc}
     *
     * @param Directive $directive
     */
    final protected function processSub(
        BlockContext $blockContext,
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node {
        return $this->createNode(
            new DirectiveNode(
                $directive,
                $collectionNode->getChildren(),
            ),
        );
    }

    public function createNode(DirectiveNode $directiveNode): Node
    {
        return new VersionChangeNode(
            $this->type,
            $this->label,
            $directiveNode->getDirective()->getData(),
            $directiveNode->getChildren(),
        );
    }
}

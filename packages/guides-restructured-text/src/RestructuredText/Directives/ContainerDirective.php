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
use phpDocumentor\Guides\RestructuredText\Nodes\ContainerNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

/**
 * Divs a sub document in a div with a given class or set of classes.
 *
 * @link https://docutils.sourceforge.io/docs/ref/rst/directives.html#container
 */
final class ContainerDirective extends SubDirective
{
    public function getName(): string
    {
        return 'container';
    }

    /** {@inheritDoc} */
    public function getAliases(): array
    {
        return ['div'];
    }

    /** {@inheritDoc}
     *
     * @param Directive $directive
     */
    protected function processSub(
        BlockContext $blockContext,
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        return (new ContainerNode($collectionNode->getChildren()))->withOptions(['class' => $directive->getData()]);
    }
}

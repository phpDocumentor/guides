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

namespace phpDocumentor\Guides\Bootstrap\Directives;

use phpDocumentor\Guides\Bootstrap\Nodes\Card\CardImageNode;
use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Directives\SubDirective;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

class CardImageDirective extends SubDirective
{
    public function getName(): string
    {
        return 'card-image';
    }

    protected function processSub(
        BlockContext $blockContext,
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        $alt = '';
        if ($directive->hasOption('alt')) {
            $alt = $directive->getOption('alt')->toString();
        }

        $position = 'top';
        if ($directive->hasOption('position')) {
            $position = $directive->getOption('position')->toString();
        }

        $overlay = $collectionNode->getChildren();

        return new CardImageNode(
            $this->getName(),
            $directive->getData(),
            $directive->getDataNode() ?? new InlineCompoundNode(),
            $alt,
            $position,
            $overlay,
        );
    }
}

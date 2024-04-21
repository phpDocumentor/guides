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

use phpDocumentor\Guides\Bootstrap\Nodes\Card\CardFooterNode;
use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\Inline\AbstractLinkInlineNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Directives\SubDirective;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

use function array_merge;
use function explode;

class CardFooterDirective extends SubDirective
{
    public function getName(): string
    {
        return 'card-footer';
    }

    protected function processSub(
        BlockContext $blockContext,
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        $contentItems = [];
        $buttonStyle = null;
        if ($directive->hasOption('button-style')) {
            $buttonStyle = $directive->getOption('button-style')->toString();
            if ($buttonStyle === '') {
                $buttonStyle = 'btn btn-primary';
            }

            $buttonStyle = explode(' ', $buttonStyle);
        }

        if ($directive->getDataNode() !== null) {
            $content = $directive->getDataNode();
            foreach ($content->getChildren() as $contentItem) {
                if ($buttonStyle !== null && $contentItem instanceof AbstractLinkInlineNode) {
                    $contentItem->setClasses(array_merge($contentItem->getClasses(), $buttonStyle));
                }

                $contentItems[] = $contentItem;
            }
        }

        return new CardFooterNode(
            $this->getName(),
            $directive->getData(),
            new InlineCompoundNode($contentItems),
            $collectionNode->getChildren(),
        );
    }
}

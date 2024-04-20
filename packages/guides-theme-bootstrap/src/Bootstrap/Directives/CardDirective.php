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
use phpDocumentor\Guides\Bootstrap\Nodes\Card\CardHeaderNode;
use phpDocumentor\Guides\Bootstrap\Nodes\Card\CardImageNode;
use phpDocumentor\Guides\Bootstrap\Nodes\CardNode;
use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\ReferenceResolvers\AnchorNormalizer;
use phpDocumentor\Guides\RestructuredText\Directives\SubDirective;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;
use phpDocumentor\Guides\RestructuredText\TextRoles\GenericLinkProvider;

class CardDirective extends SubDirective
{
    public const NAME = 'card';

    public function __construct(
        protected Rule $startingRule,
        GenericLinkProvider $genericLinkProvider,
        private readonly AnchorNormalizer $anchorReducer,
    ) {
        parent::__construct($startingRule);

        $genericLinkProvider->addGenericLink(self::NAME, CardNode::LINK_TYPE, CardNode::LINK_PREFIX);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    protected function processSub(
        BlockContext $blockContext,
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        $title = null;
        if ($directive->getDataNode() !== null) {
            $title = new TitleNode($directive->getDataNode(), 3, $this->getName());
            $title->setClasses(['card-title']);
        }

        $originalChildren = $collectionNode->getChildren();
        $children = [];
        $header = null;
        $image = null;
        $footer = null;
        foreach ($originalChildren as $child) {
            if ($child instanceof CardHeaderNode) {
                $header = $child;
            } elseif ($child instanceof CardImageNode) {
                $image = $child;
            } elseif ($child instanceof CardFooterNode) {
                $footer = $child;
            } else {
                $children[] = $child;
            }
        }

        $id = '';
        if ($directive->hasOption('name')) {
            $id = $directive->getOption('name')->toString();
        }

        $id = $this->anchorReducer->reduceAnchor($id);

        return new CardNode(
            $this->getName(),
            $directive->getData(),
            $directive->getDataNode() ?? new InlineCompoundNode(),
            $title,
            $children,
            $header,
            $image,
            $footer,
            $id,
        );
    }
}

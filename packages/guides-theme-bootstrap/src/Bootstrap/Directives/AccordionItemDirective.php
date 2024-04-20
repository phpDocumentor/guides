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

use phpDocumentor\Guides\Bootstrap\Nodes\AccordionItemNode;
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
use Psr\Log\LoggerInterface;

use function intval;

class AccordionItemDirective extends SubDirective
{
    public const NAME = 'accordion-item';

    public function __construct(
        protected Rule $startingRule,
        GenericLinkProvider $genericLinkProvider,
        private readonly AnchorNormalizer $anchorReducer,
        private readonly LoggerInterface $logger,
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
        $headerLevel = intval($directive->getOption('header-level')->getValue());
        if ($headerLevel <= 0) {
            $headerLevel = 3;
        }

        if ($directive->getDataNode() !== null) {
            $title = new TitleNode($directive->getDataNode(), $headerLevel, $this->getName());
        } else {
            $title = TitleNode::fromString('Accordion Item')->setLevel($headerLevel);
            $this->logger->warning('An accordion item must have a title. Usage: ..  accordion-item:: [title] ', $blockContext->getLoggerInformation());
        }

        $children = $collectionNode->getChildren();

        $id = $directive->getOption('name')->toString();
        $show = $directive->hasOption('show');
        if ($id === '') {
            $id = 'accordion';
            $this->logger->warning('An accordion item must have a unique name as parameter. ', $blockContext->getLoggerInformation());
        }

        $id = $this->anchorReducer->reduceAnchor($id);

        return new AccordionItemNode(
            $this->getName(),
            $directive->getData(),
            $directive->getDataNode() ?? new InlineCompoundNode(),
            $title,
            $children,
            $id,
            $show,
        );
    }
}

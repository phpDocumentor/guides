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
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\ReferenceResolvers\AnchorNormalizer;
use phpDocumentor\Guides\RestructuredText\Nodes\AbstractTabNode;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;
use phpDocumentor\Guides\RestructuredText\Nodes\TabsNode;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;
use Psr\Log\LoggerInterface;

use function class_alias;
use function class_exists;
use function is_string;

#[Attributes\Directive(name: 'tabs')]
final class TabsDirective extends SubDirective
{
    private int $tabsCounter = 0;

    /** @param Rule<CollectionNode> $startingRule */
    public function __construct(
        protected Rule $startingRule,
        private readonly LoggerInterface $logger,
        private readonly AnchorNormalizer $anchorReducer,
    ) {
        parent::__construct($startingRule);
    }

    public function createNode(DirectiveNode $directiveNode): Node
    {
        $directive = $directiveNode->getDirective();
        $tabs = [];
        $hasActive = false;
        foreach ($directiveNode->getChildren() as $child) {
            if ($child instanceof AbstractTabNode) {
                if ($child->isActive()) {
                    if (!$hasActive) {
                        $hasActive = true;
                    } else {
                        // There may only be one active child, first wins
                        $child->setActive(false);
                    }
                }

                $tabs[] = $child;
            } else {
                $this->logger->warning(
                    'The "tabs" directive may only contain children of type "tab". The following node was found: ' . $child::class,
                    $directiveNode->getSourceLocation()->toLoggerInformation(),
                );
            }
        }

        if (!$hasActive && isset($tabs[0])) {
            $tabs[0]->setActive(true);
        }

        if (is_string($directive->getOption('key')->getValue())) {
            $key = $this->anchorReducer->reduceAnchor($directive->getOption('key')->getValue());
        } else {
            $this->tabsCounter++;
            $key = 'tabs-' . $this->tabsCounter;
        }

        return new TabsNode(
            'tabs',
            $directive->getData(),
            $directive->getDataNode() ?? new InlineCompoundNode(),
            $key,
            $tabs,
        );
    }
}

if (!class_exists(\phpDocumentor\Guides\Bootstrap\Directives\TabsDirective::class, false)) {
    class_alias(TabsDirective::class, \phpDocumentor\Guides\Bootstrap\Directives\TabsDirective::class);
}

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

use phpDocumentor\Guides\Bootstrap\Nodes\TabNode;
use phpDocumentor\Guides\Bootstrap\Nodes\TabsNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Directives\SubDirective;
use phpDocumentor\Guides\RestructuredText\Nodes\CollectionNode;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;
use Psr\Log\LoggerInterface;

use function is_string;
use function preg_replace;
use function rand;
use function str_replace;
use function strtolower;
use function strval;

class TabsDirective extends SubDirective
{
    /** @param Rule<CollectionNode> $startingRule */
    public function __construct(protected Rule $startingRule, private readonly LoggerInterface $logger)
    {
        parent::__construct($startingRule);
    }

    public function getName(): string
    {
        return 'tabs';
    }

    /** {@inheritDoc}
     *
     * @param Directive $directive
     */
    protected function processSub(
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        $tabs = [];
        $hasActive = false;
        foreach ($collectionNode->getChildren() as $child) {
            if ($child instanceof TabNode) {
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
                $this->logger->warning('The "tabs" directive may only contain children of type "tab". The following node was found: ' . $child::class);
            }
        }

        if (!$hasActive && isset($tabs[0])) {
            $tabs[0]->setActive(true);
        }

        if (is_string($directive->getOption('key')->getValue())) {
            $key = strtolower($directive->getOption('key')->getValue());
            $key = str_replace(' ', '-', $key);
            $key = strval(preg_replace('/[^a-zA-Z0-9\-_]/', '', $key));
        } else {
            $key = 'tabs-' . rand(1, 1000);
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

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
use phpDocumentor\Guides\Bootstrap\Nodes\AccordionNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Directives\Attributes\Directive;
use phpDocumentor\Guides\RestructuredText\Directives\SubDirective;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;
use Psr\Log\LoggerInterface;

#[Directive(name: 'accordion')]
class AccordionDirective extends SubDirective
{
    public const NAME = 'accordion';

    public function __construct(
        protected Rule $startingRule,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct($startingRule);
    }

    public function createNode(DirectiveNode $directiveNode): Node
    {
        $directive = $directiveNode->getDirective();

        $children = [];
        foreach ($directiveNode->getChildren() as $child) {
            if ($child instanceof AccordionItemNode) {
                $children[] = $child;
            } else {
                $this->logger->warning('An accordion may only accordion-items. ', $directiveNode->getSourceLocation()->toLoggerInformation());
            }
        }

        $id = $directive->getOption('name')->toString();
        if ($id === '') {
            $id = 'accordion';
            $this->logger->warning('An accordion must have a unique name as parameter. ', $directiveNode->getSourceLocation()->toLoggerInformation());
        }

        return new AccordionNode(
            $this->getName(),
            $directive->getData(),
            $directive->getDataNode() ?? new InlineCompoundNode(),
            $children,
            $id,
        );
    }
}

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
use phpDocumentor\Guides\RestructuredText\Nodes\ContainerNode;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;
use Psr\Log\LoggerInterface;

/**
 * Divs a sub document in a div with a given class or set of classes.
 *
 * @link https://docutils.sourceforge.io/docs/ref/rst/directives.html#container
 */
#[Attributes\Directive(name: 'testlogger')]
final class TestLoggerDirective extends SubDirective
{
    public function __construct(
        protected Rule $startingRule,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct($startingRule);
    }

    public function createNode(DirectiveNode $directiveNode): Node
    {
        $this->logger->warning('Test logging in directives', $directiveNode->getSourceLocation()->toLoggerInformation());

        return (new ContainerNode($directiveNode->getChildren()))
            ->withOptions(['class' => $directiveNode->getDirective()->getData()]);
    }
}

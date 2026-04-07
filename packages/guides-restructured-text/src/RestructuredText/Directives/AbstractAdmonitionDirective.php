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

use Doctrine\Deprecations\Deprecation;
use phpDocumentor\Guides\Nodes\AdmonitionNode;
use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;

use function array_unshift;

abstract class AbstractAdmonitionDirective extends SubDirective
{
    public function __construct(protected Rule $startingRule, private readonly string $name, private readonly string $text)
    {
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
    ): Node|null {
        return $this->createNode(
            new DirectiveNode(
                $directive,
                $collectionNode->getChildren(),
            ),
        );
    }

    public function createNode(DirectiveNode $directiveNode): Node|null
    {
        $children = $directiveNode->getChildren();
        if ($directiveNode->getDirective()->getDataNode() !== null) {
            array_unshift($children, new ParagraphNode([$directiveNode->getDirective()->getDataNode()]));
        }

        return new AdmonitionNode(
            $directiveNode->getDirective()->getName(),
            null,
            $this->text,
            $children,
        );
    }
}

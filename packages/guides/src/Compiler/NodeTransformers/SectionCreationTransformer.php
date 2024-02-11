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

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\DocumentTree\SectionEntryNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Nodes\SectionNode;

use phpDocumentor\Guides\Nodes\TitleNode;
use function array_pop;
use function assert;
use function count;
use function end;

/** @implements NodeTransformer<Node> */
final class SectionCreationTransformer implements NodeTransformer
{
    /** @var SectionNode[] $sectionStack */
    private array $sectionStack = [];


    public function enterNode(Node $node, CompilerContext $compilerContext): Node
    {
        if (!$compilerContext->getShadowTree()->getParent()?->getNode() instanceof DocumentNode) {
            return $node;
        }
        if ($node instanceof TitleNode) {
            if (count($this->sectionStack) === 0) {
                $this->sectionStack[] = new SectionNode($node);
            }
        }

        $lastSection = end($this->sectionStack);
        if ($lastSection instanceof SectionNode) {
            $lastSection->addChildNode($node);
        }

        return $node;
    }

    public function leaveNode(Node $node, CompilerContext $compilerContext): Node|null
    {
        if (!$compilerContext->getShadowTree()->getParent()?->getNode() instanceof DocumentNode) {
            return $node;
        }
        // Try removing all nodes...
        return null;
        /*
        if (count($this->sectionStack) === 0) {
            return $node;
        }
        if ($node instanceof TitleNode) {
            return array_pop($this->sectionStack);
        }

        return null;
        */
    }

    public function supports(Node $node): bool
    {
        return true;
    }

    public function getPriority(): int
    {
        // Before SectionEntryRegistrationTransformer
        return 1;
    }
}

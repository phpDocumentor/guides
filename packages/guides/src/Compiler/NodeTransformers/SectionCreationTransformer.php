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

use phpDocumentor\Guides\Compiler\CompilerContextInterface;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\TitleNode;

use function array_pop;
use function count;
use function end;

use const PHP_INT_MAX;

/** @implements NodeTransformer<Node> */
final class SectionCreationTransformer implements NodeTransformer
{
    /** @var SectionNode[] $sectionStack */
    private array $sectionStack = [];
    private int $firstLevel = 1;

    public function enterNode(Node $node, CompilerContextInterface $compilerContext): Node
    {
        if ($node instanceof DocumentNode) {
            $this->firstLevel = 1;
            $this->sectionStack = [];
        }

        if (!$compilerContext->getShadowTree()->getParent()?->getNode() instanceof DocumentNode) {
            return $node;
        }

        if (!$node instanceof TitleNode) {
            $lastSection = end($this->sectionStack);
            if ($lastSection instanceof SectionNode) {
                $lastSection->addChildNode($node);
            }
        }

        return $node;
    }

    public function leaveNode(Node $node, CompilerContextInterface $compilerContext): Node|null
    {
        if (!$compilerContext->getShadowTree()->getParent()?->getNode() instanceof DocumentNode) {
            return $node;
        }

        if ($node instanceof SectionNode) {
            return $node;
        }

        if (count($this->sectionStack) === 0 && !$node instanceof TitleNode) {
            return $node;
        }

        if (count($this->sectionStack) > 0 && $compilerContext->getShadowTree()->isLastChildOfParent()) {
            $lastSection = end($this->sectionStack);
            while ($lastSection?->getTitle()->getLevel() > $this->firstLevel) {
                $lastSection = array_pop($this->sectionStack);
            }

            return $lastSection;
        }

        if (!$node instanceof TitleNode) {
            // Remove all nodes that will be attached to a section
            return null;
        }

        $lastSection = end($this->sectionStack);
        if ($lastSection instanceof SectionNode && $node !== $lastSection->getTitle() && $node->getLevel() <= $lastSection->getTitle()->getLevel()) {
            while (end($this->sectionStack) instanceof SectionNode && $node !== end($this->sectionStack)->getTitle() && $node->getLevel() <= end($this->sectionStack)->getTitle()->getLevel()) {
                $lastSection = array_pop($this->sectionStack);
            }

            $newSection = new SectionNode($node);
            // Attach the new section to the last one still on the stack if there still is one
            if (end($this->sectionStack) instanceof SectionNode) {
                end($this->sectionStack)->addChildNode($newSection);
            }

            $this->pushNewSectionToStack($newSection);

            return $lastSection?->getTitle()->getLevel() <= $this->firstLevel ? $lastSection : null;
        }

        $newSection = new SectionNode($node);
        if ($lastSection instanceof SectionNode) {
            $lastSection->addChildNode($newSection);
        }

        $this->pushNewSectionToStack($newSection);

        return null;
    }

    public function supports(Node $node): bool
    {
        return true;
    }

    public function getPriority(): int
    {
        // Should run as first transformer
        return PHP_INT_MAX;
    }

    /**
     * Pushes the new section to the stack.
     *
     * The stack is used to track the current level of nodes and adding child
     * nodes to the section. As not all documentation formats are using the
     * correct level of title nodes we need to track the level of the first
     * title node to determine the correct level of the section.
     */
    private function pushNewSectionToStack(SectionNode $newSection): void
    {
        if (count($this->sectionStack) === 0) {
            $this->firstLevel = $newSection->getTitle()->getLevel();
        }

        $this->sectionStack[] = $newSection;
    }
}

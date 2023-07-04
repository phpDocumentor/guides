<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\DocumentTree\SectionEntryNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\SectionNode;

use function array_pop;
use function assert;
use function count;
use function end;

/** @implements NodeTransformer<Node> */
class SectionEntryRegistrationTransformer implements NodeTransformer
{
    /** @var SectionEntryNode[] $sectionStack */
    private array $sectionStack = [];

    public function enterNode(Node $node, CompilerContext $compilerContext): Node
    {
        if (!$node instanceof SectionNode) {
            return $node;
        }

        $sectionEntryNode = new SectionEntryNode($node->getTitle());
        if (count($this->sectionStack) === 0) {
            $compilerContext->getDocumentNode()->getDocumentEntry()->addSection($sectionEntryNode);
        } else {
            $parentSection = end($this->sectionStack);
            assert($parentSection instanceof SectionEntryNode);
            $parentSection->addChild($sectionEntryNode);
        }

        $this->sectionStack[] = $sectionEntryNode;

        return $node;
    }

    public function leaveNode(Node $node, CompilerContext $compilerContext): Node|null
    {
        if (!$node instanceof SectionNode) {
            return $node;
        }

        array_pop($this->sectionStack);

        return $node;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof SectionNode;
    }

    public function getPriority(): int
    {
        // After DocumentEntryRegistrationTransformer
        return 4900;
    }
}

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
use phpDocumentor\Guides\Nodes\DocumentTree\SectionEntryNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\SectionNode;

use function array_pop;
use function assert;
use function count;
use function end;

/** @implements NodeTransformer<Node> */
final class SectionEntryRegistrationTransformer implements NodeTransformer
{
    /** @var SectionEntryNode[] $sectionStack */
    private array $sectionStack = [];

    public function enterNode(Node $node, CompilerContextInterface $compilerContext): Node
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

    public function leaveNode(Node $node, CompilerContextInterface $compilerContext): Node|null
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

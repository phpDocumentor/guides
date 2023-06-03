<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\DocumentBlockNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TocNode;

use function array_merge;

/**
 * @implements NodeTransformer<Node>
 *
 * The "class" directive sets the "classes" attribute value on its content or on the first immediately following
 * non-comment element. https://docutils.sourceforge.io/docs/ref/rst/directives.html#class
 */
class DocumentBlockNodeTransformer implements NodeTransformer
{
    public function enterNode(Node $node, DocumentNode $documentNode, CompilerContext $compilerContext): Node
    {
        return $node;
    }

    public function leaveNode(Node $node, DocumentNode $documentNode, CompilerContext $compilerContext): Node|null
    {
        if ($node instanceof DocumentBlockNode) {
            $children = [];
            foreach ($node->getValue() as $child) {
                if ($child instanceof TocNode) {
                    $child = $child->withOptions(array_merge($child->getOptions(), ['menu' => $node->getIdentifier()]));
                }

                $child = $child->withOptions(array_merge($child->getOptions(), ['documentBlock' => $node->getIdentifier()]));

                $children[] = $child;
            }

            $documentNode->addDocumentPart($node->getIdentifier(), $children);

            // Remove the node as it should not be rendered in the defined place but
            // wherever the theme defines
            return null;
        }

        return $node;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof DocumentBlockNode;
    }

    public function getPriority(): int
    {
        return 3000;
    }
}

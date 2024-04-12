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
use phpDocumentor\Guides\Nodes\DocumentBlockNode;
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\Nodes\Node;

/**
 * @implements NodeTransformer<Node>
 *
 * The "class" directive sets the "classes" attribute value on its content or on the first immediately following
 * non-comment element. https://docutils.sourceforge.io/docs/ref/rst/directives.html#class
 */
final class DocumentBlockNodeTransformer implements NodeTransformer
{
    public function enterNode(Node $node, CompilerContextInterface $compilerContext): Node
    {
        return $node;
    }

    public function leaveNode(Node $node, CompilerContextInterface $compilerContext): Node|null
    {
        if ($node instanceof DocumentBlockNode) {
            $children = [];
            foreach ($node->getValue() as $child) {
                if ($child instanceof TocNode) {
                    $child = $child->withOptions([...$child->getOptions(), 'menu' => $node->getIdentifier()]);
                }

                $child = $child->withOptions([...$child->getOptions(), 'documentBlock' => $node->getIdentifier()]);

                $children[] = $child;
            }

            $compilerContext->getDocumentNode()->addDocumentPart($node->getIdentifier(), $children);

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

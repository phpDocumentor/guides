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
use phpDocumentor\Guides\Nodes\ClassNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;

use function array_merge;

/**
 * @implements NodeTransformer<Node>
 *
 * The "class" directive sets the "classes" attribute value on its content or on the first immediately following
 * non-comment element. https://docutils.sourceforge.io/docs/ref/rst/directives.html#class
 */
final class ClassNodeTransformer implements NodeTransformer
{
    /** @var string[] */
    private array $classes = [];

    public function enterNode(Node $node, CompilerContextInterface $compilerContext): Node
    {
        if ($node instanceof DocumentNode) {
            // unset classes when entering the next document
            $this->classes = [];
        }

        if ($node instanceof ClassNode) {
            $this->classes = $node->getClasses();
        }

        if ($this->classes !== [] && !$node instanceof ClassNode) {
            $node->setClasses(array_merge($node->getClasses(), $this->classes));
            // Unset the classes after applied to the first direct successor
            $this->classes = [];
        }

        return $node;
    }

    public function leaveNode(Node $node, CompilerContextInterface $compilerContext): Node|null
    {
        if ($node instanceof ClassNode) {
            //Remove the class node from the tree.
            return null;
        }

        return $node;
    }

    public function supports(Node $node): bool
    {
        // Every node can have a class attached to it, however the node renderer decides on if to render the class
        return true;
    }

    public function getPriority(): int
    {
        return 40_000;
    }
}

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

namespace phpDocumentor\Guides\Compiler;

use phpDocumentor\Guides\Nodes\Node;

/** @template T of Node */
interface NodeTransformer
{
    /**
     * @param T $node
     *
     * @return T
     */
    public function enterNode(Node $node, CompilerContext $compilerContext): Node;

    /**
     * @param T $node
     *
     * @return T|null
     */
    public function leaveNode(Node $node, CompilerContext $compilerContext): Node|null;

    /** @psalm-assert-if-true T $node */
    public function supports(Node $node): bool;

    /**
     * The higher the priority the earlier the NodeTransformer is executed.
     */
    public function getPriority(): int;
}

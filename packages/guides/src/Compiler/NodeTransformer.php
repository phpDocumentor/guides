<?php

declare(strict_types=1);

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
    public function enterNode(Node $node): Node;

    /**
     * @param T $node
     *
     * @return T|null
     */
    public function leaveNode(Node $node): ?Node;

    /**
     * @psalm-assert-if-true T $node
     */
    public function supports(Node $node): bool;
}

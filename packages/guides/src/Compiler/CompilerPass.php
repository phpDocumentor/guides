<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler;

use phpDocumentor\Guides\Nodes\DocumentNode;

interface CompilerPass
{
    /**
     * @param DocumentNode[] $documents
     * @return DocumentNode[]
     */
    public function run(array $documents): array;

    public function getPriority(): int;
}

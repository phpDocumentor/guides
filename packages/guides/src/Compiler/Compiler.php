<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler;

use phpDocumentor\Guides\Nodes\DocumentNode;
use SplPriorityQueue;

class Compiler
{
    /** @var SplPriorityQueue<int, CompilerPass> */
    private SplPriorityQueue $passes;

    /** @param iterable<CompilerPass> $passes */
    public function __construct(iterable $passes)
    {
        $this->passes = new SplPriorityQueue();
        foreach ($passes as $pass) {
            $this->passes->insert($pass, $pass->getPriority());
        }
    }

    /**
     * @param DocumentNode[] $documents
     *
     * @return DocumentNode[]
     */
    public function run(array $documents): array
    {
        $clonedPasses = clone$this->passes;
        foreach ($clonedPasses as $pass) {
            $documents = $pass->run($documents);
        }

        return $documents;
    }
}

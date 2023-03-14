<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Handlers;

use phpDocumentor\Guides\Compiler\Compiler;
use phpDocumentor\Guides\Nodes\DocumentNode;

final class CompileDocumentsHandler
{
    private Compiler $compiler;

    public function __construct(Compiler $compiler)
    {
        $this->compiler = $compiler;
    }

    /** @return DocumentNode[] */
    public function handle(CompileDocumentsCommand $command): array
    {
        return $this->compiler->run($command->getDocuments());
    }
}

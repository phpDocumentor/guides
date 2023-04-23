<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Handlers;

use phpDocumentor\Guides\Nodes\DocumentNode;

final class CompileDocumentsCommand
{
    /** @param DocumentNode[] $documents */
    public function __construct(private readonly array $documents)
    {
    }

    /** @return DocumentNode[] */
    public function getDocuments(): array
    {
        return $this->documents;
    }
}

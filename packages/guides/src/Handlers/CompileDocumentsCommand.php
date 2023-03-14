<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Handlers;

use phpDocumentor\Guides\Nodes\DocumentNode;

final class CompileDocumentsCommand
{
    /** @var DocumentNode[] */
    private array $documents;

    /** @param DocumentNode[] $documents */
    public function __construct(array $documents)
    {
        $this->documents = $documents;
    }

    /** @return DocumentNode[] */
    public function getDocuments(): array
    {
        return $this->documents;
    }
}

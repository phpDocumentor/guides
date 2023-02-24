<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\Passes;

use phpDocumentor\Guides\Compiler\DocumentNodeTraverser;
use phpDocumentor\Guides\Compiler\CompilerPass;
use phpDocumentor\Guides\Nodes\DocumentNode;

final class TransformerPass implements CompilerPass
{
    private DocumentNodeTraverser $documentNodeTraverser;

    public function __construct(DocumentNodeTraverser $documentNodeTraverser)
    {
        $this->documentNodeTraverser = $documentNodeTraverser;
    }

    public function run(array $documents): array
    {
        foreach ($documents as $key => $document) {
            if ($document instanceof DocumentNode) {
                $documents[$key] = $this->documentNodeTraverser->traverse($document);
            }
        }

        return array_filter($documents, fn ($document): bool => $document instanceof DocumentNode);
    }

    public function getPriority(): int
    {
        return 1000;
    }
}

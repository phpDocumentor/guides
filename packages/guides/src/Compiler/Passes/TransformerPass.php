<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\Passes;

use phpDocumentor\Guides\Compiler\CompilerPass;
use phpDocumentor\Guides\Compiler\DocumentNodeTraverser;
use phpDocumentor\Guides\Nodes\DocumentNode;

use function array_filter;

final class TransformerPass implements CompilerPass
{
    private DocumentNodeTraverser $documentNodeTraverser;

    public function __construct(DocumentNodeTraverser $documentNodeTraverser)
    {
        $this->documentNodeTraverser = $documentNodeTraverser;
    }

    /** {@inheritDoc} */
    public function run(array $documents): array
    {
        foreach ($documents as $key => $document) {
            if (!($document instanceof DocumentNode)) {
                continue;
            }

            $documents[$key] = $this->documentNodeTraverser->traverse($document);
        }

        return array_filter($documents, static fn ($document): bool => $document instanceof DocumentNode);
    }

    public function getPriority(): int
    {
        return 1000;
    }
}

<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler;

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
            $documents[$key] = $this->documentNodeTraverser->traverse($document);
        }

        return array_filter($documents);
    }

    public function getPriority(): int
    {
        return 1000;
    }
}

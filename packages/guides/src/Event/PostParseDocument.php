<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Event;

use phpDocumentor\Guides\Nodes\DocumentNode;

final class PostParseDocument
{
    public function __construct(private string $fileName, private DocumentNode|null $documentNode)
    {
    }

    public function getDocumentNode(): DocumentNode|null
    {
        return $this->documentNode;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }
}

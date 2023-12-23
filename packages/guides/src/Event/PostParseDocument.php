<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Event;

use phpDocumentor\Guides\Nodes\DocumentNode;

/**
 * This event is called after the parsing of each document is completed by the responsible extension.
 *
 * It can for example be used to display a progress bar.
 */
final class PostParseDocument
{
    public function __construct(private readonly string $fileName, private readonly DocumentNode|null $documentNode)
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

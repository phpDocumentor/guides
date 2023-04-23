<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Event;

use phpDocumentor\Guides\Parser;

final class PreParseDocument
{
    public function __construct(private readonly Parser $parser, private readonly string $fileName, private string $contents)
    {
    }

    public function getParser(): Parser
    {
        return $this->parser;
    }

    public function setContents(string $contents): void
    {
        $this->contents = $contents;
    }

    public function getContents(): string
    {
        return $this->contents;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }
}

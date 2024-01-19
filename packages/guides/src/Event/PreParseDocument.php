<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Guides\Event;

use phpDocumentor\Guides\Parser;

/**
 * This event is called before the parsing of each document is passed to the responsible extension.
 *
 * It can be used to manipulate the content passed to the parser by calling PreParseDocument::setContents
 */
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

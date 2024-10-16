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

use phpDocumentor\Guides\Nodes\DocumentNode;

/**
 * This event is called after the parsing of each document is completed by the responsible extension.
 *
 * It can for example be used to display a progress bar.
 */
final class PostParseDocument
{
    public function __construct(
        private readonly string $fileName,
        private DocumentNode|null $documentNode,
        private readonly string $originalFile,
    ) {
    }

    public function getDocumentNode(): DocumentNode|null
    {
        return $this->documentNode;
    }

    public function setDocumentNode(DocumentNode|null $documentNode): void
    {
        $this->documentNode = $documentNode;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getOriginalFileName(): string
    {
        return $this->originalFile;
    }
}

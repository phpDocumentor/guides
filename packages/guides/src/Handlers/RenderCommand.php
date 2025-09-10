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

namespace phpDocumentor\Guides\Handlers;

use League\Flysystem\FilesystemInterface;
use phpDocumentor\FileSystem\FileSystem;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Renderer\DocumentListIterator;

final class RenderCommand
{
    private DocumentListIterator $documentIterator;

    /** @param DocumentNode[] $documentArray */
    public function __construct(
        private readonly string $outputFormat,
        private readonly array $documentArray,
        private readonly FilesystemInterface|FileSystem $origin,
        private readonly FilesystemInterface|FileSystem $destination,
        private readonly ProjectNode $projectNode,
        private readonly string $destinationPath = '/',
    ) {
        $this->documentIterator = DocumentListIterator::create(
            $this->projectNode->getRootDocumentEntry(),
            $this->documentArray,
        );
    }

    public function getOutputFormat(): string
    {
        return $this->outputFormat;
    }

    /** @return DocumentNode[] $documentArray */
    public function getDocumentArray(): array
    {
        return $this->documentArray;
    }

    public function getDocumentIterator(): DocumentListIterator
    {
        return $this->documentIterator;
    }

    public function getOrigin(): FilesystemInterface|FileSystem
    {
        return $this->origin;
    }

    public function getDestination(): FilesystemInterface|FileSystem
    {
        return $this->destination;
    }

    public function getDestinationPath(): string
    {
        return $this->destinationPath;
    }

    public function getProjectNode(): ProjectNode
    {
        return $this->projectNode;
    }
}

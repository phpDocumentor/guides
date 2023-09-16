<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Handlers;

use League\Flysystem\FilesystemInterface;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\ProjectNode;

final class RenderCommand
{
    /**
     * @param DocumentNode[] $documentArray
     * @param iterable<DocumentNode> $documentIterator
     */
    public function __construct(
        private readonly string $outputFormat,
        private readonly array $documentArray,
        private readonly iterable $documentIterator,
        private readonly FilesystemInterface $origin,
        private readonly FilesystemInterface $destination,
        private readonly ProjectNode $projectNode,
        private readonly string $destinationPath = '/',
    ) {
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

    /** @return iterable<DocumentNode> $documentIterator */
    public function getDocumentIterator(): iterable
    {
        return $this->documentIterator;
    }

    public function getOrigin(): FilesystemInterface
    {
        return $this->origin;
    }

    public function getDestination(): FilesystemInterface
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

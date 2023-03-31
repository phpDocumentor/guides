<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Handlers;

use League\Flysystem\FilesystemInterface;
use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\Nodes\DocumentNode;

final class RenderCommand
{
    private string $outputFormat;
    /** @var DocumentNode[] */
    private array $documents;
    private Metas $metas;
    private FilesystemInterface $origin;
    private FilesystemInterface $destination;
    private string $destinationPath;

    /**
     * @param DocumentNode[] $documents
     */
    public function __construct(
        string $outputFormat,
        array $documents,
        Metas $metas,
        FilesystemInterface $origin,
        FilesystemInterface $destination,
        string $destinationPath = '/'
    ) {
        $this->outputFormat = $outputFormat;
        $this->documents = $documents;
        $this->metas = $metas;
        $this->origin = $origin;
        $this->destination = $destination;
        $this->destinationPath = $destinationPath;
    }

    public function getOutputFormat(): string
    {
        return $this->outputFormat;
    }

    /**
     * @return DocumentNode[]
     */
    public function getDocuments(): array
    {
        return $this->documents;
    }

    public function getMetas(): Metas
    {
        return $this->metas;
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
}

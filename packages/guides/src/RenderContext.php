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

namespace phpDocumentor\Guides;

use Exception;
use League\Flysystem\FilesystemInterface;
use LogicException;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ProjectNode;

use function dirname;

class RenderContext
{
    private DocumentNode $document;
    /** @var DocumentNode[] */
    private array $allDocuments;

    private function __construct(
        private readonly string $destinationPath,
        private readonly string|null $currentFileName,
        private readonly FilesystemInterface $origin,
        private readonly FilesystemInterface $destination,
        private readonly string $outputFormat,
        private readonly ProjectNode $projectNode,
    ) {
    }

    /** @param DocumentNode[] $allDocumentNodes */
    public static function forDocument(
        DocumentNode $documentNode,
        array $allDocumentNodes,
        FilesystemInterface $origin,
        FilesystemInterface $destination,
        string $destinationPath,
        string $ouputFormat,
        ProjectNode $projectNode,
    ): self {
        $self = new self(
            $destinationPath,
            $documentNode->getFilePath(),
            $origin,
            $destination,
            $ouputFormat,
            $projectNode,
        );

        $self->document = $documentNode;
        $self->allDocuments = $allDocumentNodes;

        return $self;
    }

    /** @param DocumentNode[] $allDocumentNodes */
    public static function forProject(
        ProjectNode $projectNode,
        array $allDocumentNodes,
        FilesystemInterface $origin,
        FilesystemInterface $destination,
        string $destinationPath,
        string $ouputFormat,
    ): self {
        $self = new self(
            $destinationPath,
            null,
            $origin,
            $destination,
            $ouputFormat,
            $projectNode,
        );

        $self->allDocuments = $allDocumentNodes;

        return $self;
    }

    /**
     * @param TType $default
     *
     * @phpstan-return TType|string|Node
     *
     * @template TType as mixed
     */
    public function getVariable(string $variable, mixed $default = null): mixed
    {
        return $this->document->getVariable($variable, $default);
    }

    public function getLink(string $name): string
    {
        $link = $this->document->getLink($name);

        return $link ?? '';
    }

    public function getDirName(): string
    {
        $dirname = dirname($this->getCurrentFileName());

        if ($dirname === '.') {
            return '';
        }

        return $dirname;
    }

    public function getCurrentFileName(): string
    {
        if ($this->currentFileName === null) {
            throw new LogicException('Cannot get current file name when not rendering a document');
        }

        return $this->currentFileName;
    }

    /** @return array<string, string|null> */
    public function getLoggerInformation(): array
    {
        return [
            'rst-file' => $this->currentFileName,
        ];
    }

    public function getOrigin(): FilesystemInterface
    {
        return $this->origin;
    }

    public function getCurrentDocumentEntry(): DocumentEntryNode|null
    {
        return $this->projectNode->findDocumentEntry($this->getCurrentFileName());
    }

    public function getDestinationPath(): string
    {
        return $this->destinationPath;
    }

    public function getDestination(): FilesystemInterface
    {
        return $this->destination;
    }

    public function getProjectNode(): ProjectNode
    {
        return $this->projectNode;
    }

    public function getDocumentNodeForEntry(DocumentEntryNode $entryNode): DocumentNode
    {
        foreach ($this->allDocuments as $child) {
            if ($child->getDocumentEntry() === $entryNode) {
                return $child;
            }
        }

        throw new Exception('No document was found for document entry ' . $entryNode->getFile());
    }

    public function getRootDocumentNode(): DocumentNode
    {
        return $this->getDocumentNodeForEntry($this->getProjectNode()->getRootDocumentEntry());
    }

    public function getOutputFormat(): string
    {
        return $this->outputFormat;
    }
}

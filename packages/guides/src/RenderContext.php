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
use phpDocumentor\FileSystem\FileSystem;
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

    private string $outputFilePath = '';

    private Renderer\DocumentListIterator $iterator;

    private function __construct(
        private readonly string $destinationPath,
        private readonly string|null $currentFileName,
        private readonly FilesystemInterface|FileSystem $origin,
        private readonly FilesystemInterface|FileSystem $destination,
        private readonly string $outputFormat,
        private readonly ProjectNode $projectNode,
    ) {
    }

    /** @param DocumentNode[] $allDocumentNodes */
    public static function forDocument(
        DocumentNode $documentNode,
        array $allDocumentNodes,
        FilesystemInterface|FileSystem $origin,
        FilesystemInterface|FileSystem $destination,
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
        $self->outputFilePath =  $documentNode->getFilePath() . '.' . $ouputFormat;

        return $self;
    }

    public function withDocument(DocumentNode $documentNode): self
    {
        return self::forDocument(
            $documentNode,
            $this->allDocuments,
            $this->origin,
            $this->destination,
            $this->destinationPath,
            $this->outputFormat,
            $this->projectNode,
        )->withIterator($this->getIterator());
    }

    public function getDocument(): DocumentNode
    {
        return $this->document;
    }

    /** @return DocumentNode[] */
    public function getAllDocuments(): array
    {
        return $this->allDocuments;
    }

    public function withIterator(Renderer\DocumentListIterator $iterator): self
    {
        $that = clone $this;
        $that->iterator = $iterator;

        return $that;
    }

    /** @param DocumentNode[] $allDocumentNodes */
    public static function forProject(
        ProjectNode $projectNode,
        array $allDocumentNodes,
        FilesystemInterface|FileSystem $origin,
        FilesystemInterface|FileSystem $destination,
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
        $dirname = dirname($this->outputFilePath);

        if ($dirname === '.') {
            return '';
        }

        return $dirname;
    }

    public function hasCurrentFileName(): bool
    {
        return $this->currentFileName !== null;
    }

    public function getCurrentFileName(): string
    {
        if ($this->currentFileName === null) {
            throw new LogicException('Cannot get current file name when not rendering a document');
        }

        return $this->currentFileName;
    }

    /** @return string[] */
    public function getCurrentFileRootline(): array
    {
        if ($this->getCurrentDocumentEntry() === null) {
            throw new LogicException('Cannot get current document entry when not rendering a document');
        }

        $rootline = [];
        $documentEntry = $this->getCurrentDocumentEntry();
        $rootline[] = $documentEntry->getFile();
        while ($documentEntry->getParent() instanceof DocumentEntryNode) {
            $documentEntry = $documentEntry->getParent();
            $rootline[] = $documentEntry->getFile();
        }

        return $rootline;
    }

    /** @return array<string, string|null> */
    public function getLoggerInformation(): array
    {
        return [
            'rst-file' => $this->currentFileName,
        ];
    }

    public function getOrigin(): FilesystemInterface|FileSystem
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

    public function getDestination(): FilesystemInterface|FileSystem
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

    public function getIterator(): Renderer\DocumentListIterator
    {
        return $this->iterator;
    }

    public function getOutputFilePath(): string
    {
        return $this->outputFilePath;
    }

    public function withOutputFilePath(string $outputFilePath): RenderContext
    {
        $that = clone$this;
        $that->outputFilePath = $outputFilePath;

        return $that;
    }
}

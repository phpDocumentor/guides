<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes;

use Exception;
use phpDocumentor\Guides\Meta\CitationTarget;
use phpDocumentor\Guides\Meta\InternalTarget;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;

use function date;

use const DATE_RFC2822;

/** @extends CompoundNode<DocumentNode> */
class ProjectNode extends CompoundNode
{
    /**
     * Variables are replacements in a document or project.
     *
     * Variables like |project| and |version| are replaced globally
     *
     * @var array<Node>
     */
    private array $variables = [];

    /** @var array<string, CitationTarget> */
    private array $citationTargets = [];

    /** @var array<string, InternalTarget> */
    private array $internalLinkTargets = [];

    /** @var DocumentEntryNode[] */
    private array $documentEntries = [];

    public function __construct(
        private string|null $title = null,
        private string|null $version = null,
    ) {
        $this->addVariable('project', new PlainTextInlineNode($title ?? ''));
        $this->addVariable('version', new PlainTextInlineNode($version ?? ''));
        $this->addVariable('release', new PlainTextInlineNode($version ?? ''));
        $this->addVariable('last_rendered', new PlainTextInlineNode(date(DATE_RFC2822)));
        $this->addVariable('today', new PlainTextInlineNode(date(DATE_RFC2822)));

        parent::__construct();
    }

    public function getVersion(): string|null
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        $this->addVariable('version', new PlainTextInlineNode($version));
        $this->addVariable('release', new PlainTextInlineNode($version));
        $this->version = $version;
    }

    public function getTitle(): string|null
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->addVariable('project', new PlainTextInlineNode($title));
        $this->title = $title;
    }

    public function getVariable(string $name, Node|null $default): Node|null
    {
        return $this->variables[$name] ?? $default;
    }

    public function addVariable(string $name, Node $value): void
    {
        $this->variables[$name] = $value;
    }

    public function addCitationTarget(CitationTarget $target): void
    {
        $this->citationTargets[$target->getName()] = $target;
    }

    public function getCitationTarget(string $name): CitationTarget|null
    {
        return $this->citationTargets[$name] ?? null;
    }

    public function addLinkTarget(string $anchorName, InternalTarget $target): void
    {
        $this->internalLinkTargets[$anchorName] = $target;
    }

    public function getInternalTarget(string $anchorName): InternalTarget|null
    {
        return $this->internalLinkTargets[$anchorName] ?? null;
    }

    /** @return array<string, InternalTarget> */
    public function getAllInternalTargets(): array
    {
        return $this->internalLinkTargets;
    }

    public function addDocumentEntry(DocumentEntryNode $documentEntry): void
    {
        $this->documentEntries[$documentEntry->getFile()] = $documentEntry;
    }

    /** @return DocumentEntryNode[] */
    public function getAllDocumentEntries(): array
    {
        return $this->documentEntries;
    }

    public function getRootDocumentEntry(): DocumentEntryNode
    {
        foreach ($this->documentEntries as $documentEntry) {
            if ($documentEntry->isRoot()) {
                return $documentEntry;
            }
        }

        throw new Exception('No root document entry was found');
    }

    public function getDocumentEntry(string $file): DocumentEntryNode
    {
        foreach ($this->documentEntries as $documentEntry) {
            if ($documentEntry->getFile() === $file) {
                return $documentEntry;
            }
        }

        throw new Exception('No document Entry found for file ' . $file);
    }

    /** @param DocumentEntryNode[] $documentEntries */
    public function setDocumentEntries(array $documentEntries): void
    {
        $this->documentEntries = $documentEntries;
    }

    public function findDocumentEntry(string $filePath): DocumentEntryNode|null
    {
        return $this->documentEntries[$filePath] ?? null;
    }

    public function reset(): void
    {
        $this->documentEntries = [];
    }
}

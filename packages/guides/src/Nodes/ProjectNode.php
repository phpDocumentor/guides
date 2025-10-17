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

namespace phpDocumentor\Guides\Nodes;

use DateTimeImmutable;
use Exception;
use phpDocumentor\Guides\Exception\DocumentEntryNotFound;
use phpDocumentor\Guides\Exception\DuplicateLinkAnchorException;
use phpDocumentor\Guides\Meta\CitationTarget;
use phpDocumentor\Guides\Meta\InternalTarget;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\Menu\NavMenuNode;

use function array_merge;
use function array_unique;
use function sprintf;

use const DATE_RFC2822;

/** @extends CompoundNode<DocumentNode> */
final class ProjectNode extends CompoundNode
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

    /** @var array<string, array<string, InternalTarget>> */
    private array $internalLinkTargets = [];

    /** @var DocumentEntryNode[] */
    private array $documentEntries = [];
    private DateTimeImmutable $lastRendered;

    /** @var NavMenuNode[] */
    private array $globalMenues = [];

    /** @var list<string> */
    private array $keywords = [];

    public function __construct(
        private string|null $title = null,
        private string|null $version = null,
        private string|null $release = null,
        private string|null $copyright = null,
        DateTimeImmutable|null $lastRendered = null,
    ) {
        $this->lastRendered = $lastRendered ?? new DateTimeImmutable();
        $this->addVariable('project', new PlainTextInlineNode($title ?? ''));
        $this->addVariable('version', new PlainTextInlineNode($version ?? ''));
        $this->addVariable('release', new PlainTextInlineNode($release ?? ''));
        $this->addVariable('copyright', new PlainTextInlineNode($copyright ?? ''));
        $this->addVariable('last_rendered', new PlainTextInlineNode($this->lastRendered->format(DATE_RFC2822)));
        $this->addVariable('today', new PlainTextInlineNode($this->lastRendered->format(DATE_RFC2822)));

        parent::__construct();
    }

    public function getVersion(): string|null
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        $this->addVariable('version', new PlainTextInlineNode($version));
        $this->version = $version;
    }

    public function getRelease(): string|null
    {
        return $this->release;
    }

    public function setRelease(string $release): void
    {
        $this->addVariable('release', new PlainTextInlineNode($release));
        $this->release = $release;
    }

    public function getCopyright(): string|null
    {
        return $this->copyright;
    }

    public function setCopyright(string $copyright): void
    {
        $this->addVariable('copyright', new PlainTextInlineNode($copyright));
        $this->copyright = $copyright;
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

    /** @return array<string, Node> */
    public function getVariables(): array
    {
        return $this->variables;
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

    /** @throws DuplicateLinkAnchorException */
    public function addLinkTarget(string $anchorName, InternalTarget $target): void
    {
        $linkType = $target->getLinkType();
        if (!isset($this->internalLinkTargets[$linkType])) {
            $this->internalLinkTargets[$linkType] = [];
        }

        if (isset($this->internalLinkTargets[$linkType][$anchorName]) && $linkType !== 'std:title') {
            if ($this->internalLinkTargets[$linkType][$anchorName]->getDocumentPath() === $target->getDocumentPath()) {
                return;
            }

            throw new DuplicateLinkAnchorException(sprintf('Duplicate anchor "%s". There is already another anchor of that name in document "%s"', $anchorName, $this->internalLinkTargets[$linkType][$anchorName]->getDocumentPath()));
        }

        $this->internalLinkTargets[$linkType][$anchorName] = $target;
    }

    public function hasInternalTarget(string $anchorName, string $linkType = SectionNode::STD_LABEL): bool
    {
        return isset($this->internalLinkTargets[$linkType][$anchorName]);
    }

    public function getInternalTarget(string $anchorName, string $linkType = SectionNode::STD_LABEL): InternalTarget|null
    {
        return $this->internalLinkTargets[$linkType][$anchorName] ?? null;
    }

    /** @return array<string, array<string, InternalTarget>> */
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

    /** @throws DocumentEntryNotFound */
    public function getDocumentEntry(string $file): DocumentEntryNode
    {
        foreach ($this->documentEntries as $documentEntry) {
            if ($documentEntry->getFile() === $file) {
                return $documentEntry;
            }
        }

        throw new DocumentEntryNotFound('No document Entry found for file ' . $file);
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

    public function getLastRendered(): DateTimeImmutable
    {
        return $this->lastRendered;
    }

    /** @return NavMenuNode[] */
    public function getGlobalMenues(): array
    {
        return $this->globalMenues;
    }

    /** @param NavMenuNode[] $globalMenues */
    public function setGlobalMenues(array $globalMenues): ProjectNode
    {
        $this->globalMenues = $globalMenues;

        return $this;
    }

    /** @return list<string> */
    public function getKeywords(): array
    {
        return $this->keywords;
    }

    /** @param list<string> $keywords */
    public function setKeywords(array $keywords): void
    {
        $this->keywords = $keywords;
    }

    /** @param list<string> $keywords */
    public function addKeywords(array $keywords): void
    {
        $this->keywords = array_unique(array_merge($this->keywords, $keywords));
    }
}

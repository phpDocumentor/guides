<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\InlineToken;

use phpDocumentor\Guides\Meta\DocumentEntry;

/**
 * Represents a link to document
 *
 * Supported formats
 * :doc:`foo`
 * :doc:`domain:foo`
 * :doc:`foo/subdoc#anchor`
 * :doc:`custom text <foo>`
 * :doc:`custom text <domain:foo/subdoc#anchor>`
 *
 * Cross references are resolved during rendering? -> Should be compiler.
 */
class DocReferenceNode extends AbstractLinkToken
{
    public const TYPE = 'doc';
    // URL can only be resolved during rendering as it contains file endings for html / latex etc
    private string $url = '';
    // Is resolved in the compiler
    private DocumentEntry|null $documentEntry = null;

    public function __construct(
        private readonly string $id,
        private readonly string $documentLink,
        private readonly string|null $anchor = null,
        private readonly string|null $domain = null,
        private readonly string|null $text = null,
    ) {
        parent::__construct(self::TYPE, $id, []);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDocumentLink(): string
    {
        return $this->documentLink;
    }

    public function getAnchor(): string|null
    {
        return $this->anchor;
    }

    public function getDomain(): string|null
    {
        return $this->domain;
    }

    public function getText(string|null $default = null): string
    {
        return $this->text ?? $this->getTextFromDocumentEntry() ?? $this->documentLink;
    }

    private function getTextFromDocumentEntry(): string|null
    {
        if ($this->documentEntry !== null) {
            return $this->documentEntry->getTitle()->toString();
        }

        return null;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getDocumentEntry(): DocumentEntry|null
    {
        return $this->documentEntry;
    }

    public function setDocumentEntry(DocumentEntry $documentEntry): void
    {
        $this->documentEntry = $documentEntry;
    }
}

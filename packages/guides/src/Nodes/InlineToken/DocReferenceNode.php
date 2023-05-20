<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\InlineToken;

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
    private string $url = '';

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
        return $this->text ?? $default ?? $this->documentLink;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }
}

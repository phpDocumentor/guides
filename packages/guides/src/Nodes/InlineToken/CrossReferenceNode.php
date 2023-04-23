<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\InlineToken;

/**
 * This class should be moved into Nodes, but right now the span parser is producing this.
 * I just want to get started to improve reference handling
 *
 * CrossReferences are references outside a document. As parsing is file based normal references are in document,
 * refering to other documents.
 *
 * Supported formats
 * :role:`foo`
 * :role:`foo/subdoc#anchor`
 * :domain:role:`foo`
 * :role:`custom text <foo>`
 * :role:`custom text <foo/subdoc#anchor>`
 *
 * Cross references are resolved during rendering? -> Should be compiler.
 */
class CrossReferenceNode extends InlineMarkupToken
{
    public function __construct(
        private readonly string $id,
        private readonly string $role,
        private readonly string $literal,
        private readonly string|null $anchor = null,
        private readonly string|null $text = null,
        private readonly string|null $domain = null,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUrl(): string
    {
        return $this->literal;
    }

    public function getRole(): string|null
    {
        return $this->role;
    }

    public function getDomain(): string|null
    {
        return $this->domain;
    }

    public function getAnchor(): string|null
    {
        return $this->anchor;
    }

    public function getText(string|null $default = null): string
    {
        return $this->text ?? $default ?? $this->literal;
    }
}

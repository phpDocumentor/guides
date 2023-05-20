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
 * :ref:`link`
 * :ref:`domain:link`
 * :ref:`custom text <link>`
 * :ref:`custom text <domain:link>`
 *
 * Cross references are resolved during rendering? -> Should be compiler.
 */
class ReferenceNode extends AbstractLinkToken
{
    public const TYPE = 'ref';
    private string $url = '';

    public function __construct(
        private readonly string $id,
        private readonly string $referenceName,
        private readonly string|null $domain = null,
        private readonly string|null $text = null,
    ) {
        parent::__construct(self::TYPE, $id, []);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getReferenceName(): string
    {
        return $this->referenceName;
    }

    public function getDomain(): string|null
    {
        return $this->domain;
    }

    public function getText(string|null $default = null): string
    {
        return $this->text ?? $default ?? $this->referenceName;
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
